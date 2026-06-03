<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SystemSettingsController extends Controller
{
  public function index(Request $request)
  {
    // Get all settings grouped by category
    $settings = $this->getAllSettings();

    // Get tax rates
    $taxRates = json_decode($settings['tax_rates'] ?? '[]', true) ?: [['name' => 'GST', 'rate' => 18]];

    // Get last backup info
    $lastBackup = $this->getLastBackupInfo();

    // Get categories grouped by type
    $standardFixed = Category::where('main_type', 'expense')
      ->where('category_type', 'standard_fixed')
      ->active()
      ->orderBy('name')
      ->get();

    $standardEditable = Category::where('main_type', 'expense')
      ->where('category_type', 'standard_editable')
      ->active()
      ->orderBy('name')
      ->get();

    $notStandard = Category::where('main_type', 'expense')
      ->where('category_type', 'not_standard')
      ->active()
      ->orderBy('name')
      ->get();

    $income = Category::where('main_type', 'income')
      ->where('category_type', 'income')
      ->active()
      ->orderBy('name')
      ->get();

    // Get all categories for reference
    $allCategories = Category::active()
      ->orderBy('main_type')
      ->orderBy('category_type')
      ->orderBy('name')
      ->get();


    // Get settings (you can store these in database or config)
    $settings = [
      'auto_create_cycle_expenses' => true,
      'allow_additional_expenses' => true,
    ];
    return view('Admin.system_settings', compact(
      'settings',
      'taxRates',
      'lastBackup',
      'allCategories',
      'standardFixed',
      'standardEditable',
      'notStandard',
      'income'

    ));
  }

  public function save(Request $request)
  {
    $group = $request->input('group', 'general');
    $settings = $request->except(['_token', 'group']);

    try {
      DB::beginTransaction();

      foreach ($settings as $key => $value) {
        if (is_array($value)) {
          $value = json_encode($value);
        }

        // Update or create setting
        \App\Models\SystemSetting::updateOrCreate(
          ['key' => $key],
          [
            'value' => $value,
            'type' => $this->getValueType($value),
            'description' => $this->getSettingDescription($key)
          ]
        );
      }

      DB::commit();

      // Clear settings cache
      Cache::forget('system_settings');

      return response()->json([
        'success' => true,
        'message' => 'Settings saved successfully!'
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Error saving settings: ' . $e->getMessage()
      ], 500);
    }
  }

  public function testEmail(Request $request)
  {
    try {
      $adminEmail = config('mail.from.address');

      Mail::raw('This is a test email from Finance Manager System.', function ($message) use ($adminEmail) {
        $message->to($adminEmail)
          ->subject('Test Email from Finance Manager');
      });

      return response()->json([
        'success' => true,
        'message' => 'Test email sent successfully!'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error sending test email: ' . $e->getMessage()
      ], 500);
    }
  }

  public function runBackup(Request $request)
  {
    try {
      $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
      $path = storage_path('app/backups/' . $filename);

      // Ensure backup directory exists
      if (!file_exists(storage_path('app/backups'))) {
        mkdir(storage_path('app/backups'), 0755, true);
      }

      // Run mysqldump command
      $command = sprintf(
        'mysqldump --user=%s --password=%s --host=%s %s > %s',
        config('database.connections.mysql.username'),
        config('database.connections.mysql.password'),
        config('database.connections.mysql.host'),
        config('database.connections.mysql.database'),
        $path
      );

      exec($command, $output, $returnVar);

      if ($returnVar !== 0) {
        throw new \Exception('Backup failed with error code: ' . $returnVar);
      }

      // Clean old backups
      $this->cleanOldBackups();

      return response()->json([
        'success' => true,
        'message' => 'Backup completed successfully!',
        'filename' => $filename
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Backup failed: ' . $e->getMessage()
      ], 500);
    }
  }

  public function downloadBackup()
  {
    $backups = glob(storage_path('app/backups/backup_*.sql'));

    if (empty($backups)) {
      return redirect()->back()->with('error', 'No backups available');
    }

    $latestBackup = max($backups);
    $filename = basename($latestBackup);

    return response()->download($latestBackup, $filename);
  }

  public function clearCache(Request $request)
  {
    try {
      \Artisan::call('cache:clear');
      \Artisan::call('config:clear');
      \Artisan::call('view:clear');

      return response()->json([
        'success' => true,
        'message' => 'Cache cleared successfully!'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error clearing cache: ' . $e->getMessage()
      ], 500);
    }
  }

  public function optimizeDatabase(Request $request)
  {
    try {
      $tables = DB::select('SHOW TABLES');
      $optimized = 0;

      foreach ($tables as $table) {
        $tableName = reset($table);
        DB::statement("OPTIMIZE TABLE `{$tableName}`");
        $optimized++;
      }

      return response()->json([
        'success' => true,
        'message' => "Optimized {$optimized} tables successfully!"
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error optimizing database: ' . $e->getMessage()
      ], 500);
    }
  }

  public function clearLogs(Request $request)
  {
    try {
      $days = $request->input('days', 30);
      $cutoffDate = Carbon::now()->subDays($days);

      $deleted = \App\Models\ActivityLog::where('created_at', '<', $cutoffDate)->delete();

      return response()->json([
        'success' => true,
        'message' => "Cleared {$deleted} activity logs older than {$days} days."
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error clearing logs: ' . $e->getMessage()
      ], 500);
    }
  }

  private function getAllSettings()
  {
    return Cache::remember('system_settings', 3600, function () {
      $settings = \App\Models\SystemSetting::all()->pluck('value', 'key')->toArray();

      // Default settings if not in database
      $defaults = [
        'app_name' => 'Finance Manager',
        'currency' => '₹',
        'date_format' => 'd/m/Y',
        'time_format' => '12',
        'session_timeout' => '30',
        'timezone' => 'Asia/Kolkata',
        'enable_registration' => '1',
        'enable_maintenance' => '0',

        'mail_driver' => 'smtp',
        'mail_from_address' => 'noreply@example.com',
        'mail_from_name' => 'Finance Manager',
        'mail_host' => 'smtp.mailtrap.io',
        'mail_port' => '587',
        'mail_username' => '',
        'mail_password' => '',
        'mail_encryption' => 'tls',
        'notify_invoice_due' => '1',
        'notify_expense_due' => '1',
        'notify_new_user' => '1',
        'reminder_days' => '7',

        'backup_frequency' => 'weekly',
        'backup_retention' => '30',
        'backup_location' => 'local',

        'invoice_prefix' => 'INV',
        'invoice_format' => 'year_sequence',
        'payment_terms_days' => '30',
        'late_fee_percentage' => '2',
        'invoice_notes' => 'Payment is due within 30 days. Please include the invoice number with your payment.',
        'invoice_terms' => '1. All payments must be made in full within the specified due date.',
        'auto_generate_pdf' => '1',
        'send_invoice_email' => '1',

        'default_gst_rate' => '18',
        'tax_calculation_method' => 'exclusive',
        'tax_rates' => '[{"name":"GST","rate":18}]',
        'company_gstin' => '',
        'company_pan' => ''
      ];

      return array_merge($defaults, $settings);
    });
  }

  private function getLastBackupInfo()
  {
    $backups = glob(storage_path('app/backups/backup_*.sql'));

    if (empty($backups)) {
      return null;
    }

    $latestBackup = max($backups);
    $fileSize = filesize($latestBackup);
    $fileTime = filemtime($latestBackup);

    return [
      'date' => date('d M Y, h:i A', $fileTime),
      'status' => 'success',
      'size' => $this->formatBytes($fileSize),
      'filename' => basename($latestBackup)
    ];
  }

  private function cleanOldBackups()
  {
    $retentionDays = \App\Models\SystemSetting::where('key', 'backup_retention')
      ->value('value') ?? 30;

    $backups = glob(storage_path('app/backups/backup_*.sql'));
    $cutoffTime = strtotime("-{$retentionDays} days");

    foreach ($backups as $backup) {
      if (filemtime($backup) < $cutoffTime) {
        unlink($backup);
      }
    }
  }

  private function getValueType($value)
  {
    if (is_numeric($value)) {
      return 'integer';
    } elseif ($value === 'true' || $value === 'false' || $value === '1' || $value === '0') {
      return 'boolean';
    } elseif (is_array(json_decode($value, true))) {
      return 'json';
    } else {
      return 'string';
    }
  }

  private function getSettingDescription($key)
  {
    $descriptions = [
      'app_name' => 'Application name displayed in header and emails',
      'currency' => 'Default currency symbol',
      'date_format' => 'Default date display format',
      'time_format' => '12-hour or 24-hour time format',
      'session_timeout' => 'User session timeout in minutes',
      'timezone' => 'System timezone',
      'enable_registration' => 'Allow new user registration',
      'enable_maintenance' => 'Enable maintenance mode',
      'mail_driver' => 'Mail driver for sending emails',
      'mail_from_address' => 'Default sender email address',
      'mail_from_name' => 'Default sender name',
      'mail_host' => 'SMTP server host',
      'mail_port' => 'SMTP server port',
      'mail_username' => 'SMTP username',
      'mail_password' => 'SMTP password',
      'mail_encryption' => 'SMTP encryption method',
      'notify_invoice_due' => 'Send email reminders for due invoices',
      'notify_expense_due' => 'Send email reminders for due expenses',
      'notify_new_user' => 'Send notification when new user registers',
      'reminder_days' => 'Days before due date to send reminders',
      'backup_frequency' => 'Automatic backup frequency',
      'backup_retention' => 'Number of days to keep backups',
      'backup_location' => 'Backup storage location',
      'invoice_prefix' => 'Prefix for invoice numbers',
      'invoice_format' => 'Invoice number formatting style',
      'payment_terms_days' => 'Default payment terms in days',
      'late_fee_percentage' => 'Late payment fee percentage',
      'invoice_notes' => 'Default notes displayed on invoices',
      'invoice_terms' => 'Default terms and conditions for invoices',
      'auto_generate_pdf' => 'Automatically generate PDF when creating invoice',
      'send_invoice_email' => 'Automatically send email when creating invoice',
      'default_gst_rate' => 'Default GST percentage for invoices',
      'tax_calculation_method' => 'Tax calculation method',
      'tax_rates' => 'Additional tax rates in JSON format',
      'company_gstin' => 'Company GSTIN number',
      'company_pan' => 'Company PAN number'
    ];

    return $descriptions[$key] ?? null;
  }

  private function formatBytes($bytes, $precision = 2)
  {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
  }
}
