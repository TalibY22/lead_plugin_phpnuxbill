<?php
require_once __DIR__ . '/../autoload/Package.php';


register_menu("Leads", true, "Leads", 'AFTER_SETTINGS', 'glyphicon glyphicon-comment', '', '', ['Admin', 'SuperAdmin']);



$logFile = __DIR__ . '/debug.log';
function debug_log($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}


debug_log("Script started");

debug_log("Attempting to include Package.php from: " . __DIR__ . '/../autoload/Package.php');
if (file_exists(__DIR__ . '/../autoload/Package.php')) {
    debug_log("Package.php file exists");
} else {
    debug_log("ERROR: Package.php file NOT found");
}


debug_log("Checking for required functions:");
debug_log("_post function exists: " . (function_exists('_post') ? 'Yes' : 'No'));
debug_log("r2 function exists: " . (function_exists('r2') ? 'Yes' : 'No'));
debug_log("U constant is defined: " . (defined('U') ? 'Yes' : 'No'));


debug_log("Checking ORM database connection");
try {
    $db = ORM::get_db();
    debug_log("Database connection successful");
} catch (Exception $e) {
    debug_log("Database connection failed: " . $e->getMessage());
}


debug_log("About to process request URI: " . $_SERVER['REQUEST_URI']);


function ViewLeads()
{
  global $ui;
  _admin();
  $ui->assign('_title', 'Leads Management');
  $ui->assign('_system_menu', 'plugin/leads');
  $admin = Admin::_info();
  $ui->assign('_admin', $admin);
  
  // Handle search and filtering
  $status = isset($_GET['status']) ? $_GET['status'] : '';
  $source = isset($_GET['source']) ? $_GET['source'] : '';
  $search = isset($_GET['search']) ? $_GET['search'] : '';
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $limit = 20;
  $offset = ($page - 1) * $limit;
  
  // Build query
  $query = ORM::for_table('tbl_leads');
  
  if (!empty($status)) {
    $query->where('status', $status);
  }
  
  if (!empty($source)) {
    $query->where('source', $source);
  }
  
  if (!empty($search)) {
    $query->where_raw('(name LIKE ? OR phone LIKE ? OR email LIKE ?)', 
      array("%$search%", "%$search%", "%$search%"));
  }
  
  // Count total for pagination
  $totalLeads = $query->count();
  
  // Get the leads for current page
  $leads = $query->order_by_desc('created_at')
    ->offset($offset)
    ->limit($limit)
    ->find_many();
  
  // Get stats for dashboard
  $totalActive = ORM::for_table('tbl_leads')
    ->where('status', 'Active')
    ->count();
  
  $totalConverted = ORM::for_table('tbl_leads')
    ->where('status', 'Converted')
    ->count();
  
  $totalPages = ceil($totalLeads / $limit);
  
  // Assign variables to template
  $ui->assign('leads', $leads);
  $ui->assign('totalLeads', $totalLeads);
  $ui->assign('totalActive', $totalActive);
  $ui->assign('totalConverted', $totalConverted);
  $ui->assign('currentPage', $page);
  $ui->assign('totalPages', $totalPages);
  $ui->assign('status', $status);
  $ui->assign('source', $source);
  $ui->assign('search', $search);
  
  // Display template
  $ui->display('leads.tpl');
}




$requestUri = $_SERVER['REQUEST_URI'];
$queryString = parse_url($requestUri, PHP_URL_QUERY);
$action = null;
if ($queryString) {
  parse_str($queryString, $queryParameters);
  if (isset($queryParameters['action'])) {
    $action = $queryParameters['action'];
    if ($action === "add") {
      AddLead();
      exit;
    } elseif ($action === "view") {
      ViewLeads();
      exit;
    } elseif ($action === "edit") {
      EditLead();
      exit;
    } elseif ($action === "delete") {
      DeleteLead();
      exit;
    } elseif ($action === "convert") {
      ConvertLead();
      exit;
    } elseif ($action === "import") {
      ImportLeads();
      exit;
    } elseif ($action === "export") {
      ExportLeads();
      exit;
    } else {
      echo "Unknown action";
      exit;
    }
  }
}

// Default to viewing leads if no action specified
ViewLeads();
exit;


function AddLead()
{
  global $ui;
  _admin();
  $ui->assign('_title', 'Add New Lead');
  $ui->assign('_system_menu', 'plugin/leads');
  $admin = Admin::_info();
  $ui->assign('_admin', $admin);
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $name = _post('name');
    $phone = _post('phone');
    $email = _post('email');
    $address = _post('address');
    $source = _post('source');
    $status = _post('status');
    $notes = _post('notes');
    $assigned_to = _post('assigned_to');
    
    if (empty($name) || empty($phone)) {
      r2(U . 'plugin/leads&action=add', 'e', 'Name and Phone are required fields');
      exit;
    }
    
    // Create the lead
    $lead = ORM::for_table('tbl_leads')->create();
    $lead->name = $name;
    $lead->phone = $phone;
    $lead->email = $email;
    $lead->address = $address;
    $lead->source = $source;
    $lead->status = $status;
    $lead->notes = $notes;
    $lead->assigned_to = $assigned_to;
    $lead->created_at = date('Y-m-d H:i:s');
    $lead->updated_at = date('Y-m-d H:i:s');
    $lead->save();
    
    
    
    r2(U . 'plugin/leads', 's', 'Lead added successfully');
    exit;
  }
  
  // Get all staff for assignment dropdown
  $staffMembers = ORM::for_table('tbl_users')
    ->where('user_type', 'Admin')
    ->or_where('user_type', 'Sales')
    ->select('id')
    ->select('fullname')
    ->find_many();
  
  $ui->assign('staffMembers', $staffMembers);
  $ui->display('lead-add.tpl');
}


function EditLead()
{
  global $ui;
  _admin();
  $ui->assign('_title', 'Edit Lead');
  $ui->assign('_system_menu', 'plugin/leads');
  $admin = Admin::_info();
  $ui->assign('_admin', $admin);
  
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  
  if ($id <= 0) {
    r2(U . 'plugin/leads', 'e', 'Invalid Lead ID');
    exit;
  }
  
  $lead = ORM::for_table('tbl_leads')
    ->find_one($id);
  
  if (!$lead) {
    r2(U . 'plugin/leads', 'e', 'Lead not found');
    exit;
  }
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update the lead
    $lead->name = _post('name');
    $lead->phone = _post('phone');
    $lead->email = _post('email');
    $lead->address = _post('address');
    $lead->source = _post('source');
    $lead->status = _post('status');
    $lead->notes = _post('notes');
    $lead->assigned_to = _post('assigned_to');
    $lead->updated_at = date('Y-m-d H:i:s');
    $lead->save();
    
   
    
    r2(U . 'plugin/leads', 's', 'Lead updated successfully');
    exit;
  }
  
  // Get all staff for assignment dropdown
  $staffMembers = ORM::for_table('tbl_users')
    ->where('user_type', 'Admin')
    ->or_where('user_type', 'Sales')
    ->select('id')
    ->select('fullname')
    ->find_many();
  
  $ui->assign('lead', $lead);
  $ui->assign('staffMembers', $staffMembers);
  $ui->display('lead-edit.tpl');
}

function DeleteLead()
{
  _admin();
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  
  if ($id <= 0) {
    r2(U . 'plugin/leads', 'e', 'Invalid Lead ID');
    exit;
  }
  
  $lead = ORM::for_table('tbl_leads')
    ->find_one($id);
  
  if (!$lead) {
    r2(U . 'plugin/leads', 'e', 'Lead not found');
    exit;
  }
  
  // Store lead info for notification
  $name = $lead->name;
  $phone = $lead->phone;
  
  // Delete the lead
  $lead->delete();
  
 
  
  r2(U . 'plugin/leads', 's', 'Lead deleted successfully');
}

function ConvertLead()
{
  global $ui;
  _admin();
  $ui->assign('_title', 'Convert Lead');
  $ui->assign('_system_menu', 'plugin/leads');
  $admin = Admin::_info();
  $ui->assign('_admin', $admin);
  
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  
  if ($id <= 0) {
    r2(U . 'plugin/leads', 'e', 'Invalid Lead ID');
    exit;
  }
  
  $lead = ORM::for_table('tbl_leads')
    ->find_one($id);
  
  if (!$lead) {
    r2(U . 'plugin/leads', 'e', 'Lead not found');
    exit;
  }
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create a new customer
    $customer = ORM::for_table('tbl_customers')->create();
    $customer->username = _post('username');
    $customer->password = _post('password');
    $customer->fullname = $lead->name;
    $customer->address = $lead->address;
    $customer->phonenumber = $lead->phone;
    $customer->email = $lead->email;
    $customer->status = 'Active';
    $customer->created_at = date('Y-m-d H:i:s');
    $customer->save();
    
    // Update lead status to converted
    $lead->status = 'Converted';
    $lead->customer_id = $customer->id;
    $lead->converted_at = date('Y-m-d H:i:s');
    $lead->updated_at = date('Y-m-d H:i:s');
    $lead->save();
    
   
    
    r2(U . 'customers/view/' . $customer->id, 's', 'Lead converted to customer successfully');
    exit;
  }
  
  $ui->assign('lead', $lead);
  $ui->display('lead-convert.tpl');
}

function ImportLeads()
{
  global $ui;
  _admin();
  $ui->assign('_title', 'Import Leads');
  $ui->assign('_system_menu', 'plugin/leads');
  $admin = Admin::_info();
  $ui->assign('_admin', $admin);
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if ($_FILES['csv_file']['error'] > 0) {
      r2(U . 'plugin/leads&action=import', 'e', 'Error uploading file');
      exit;
    }
    
    $file = $_FILES['csv_file']['tmp_name'];
    
    if (($handle = fopen($file, "r")) !== FALSE) {
      // Skip header row
      fgetcsv($handle, 1000, ",");
      
      $leadCount = 0;
      $errors = [];
      
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if (count($data) < 2) {
          continue; // Skip invalid rows
        }
        
        $lead = ORM::for_table('tbl_leads')->create();
        $lead->name = $data[0];
        $lead->phone = $data[1];
        $lead->email = isset($data[2]) ? $data[2] : '';
        $lead->address = isset($data[3]) ? $data[3] : '';
        $lead->source = isset($data[4]) ? $data[4] : 'Import';
        $lead->status = isset($data[5]) ? $data[5] : 'New';
        $lead->notes = isset($data[6]) ? $data[6] : '';
        $lead->assigned_to = isset($data[7]) ? $data[7] : 0;
        $lead->created_at = date('Y-m-d H:i:s');
        $lead->updated_at = date('Y-m-d H:i:s');
        
        try {
          $lead->save();
          $leadCount++;
        } catch (Exception $e) {
          $errors[] = "Error importing lead: {$data[0]} - {$e->getMessage()}";
        }
      }
      
      fclose($handle);
      
    
      
      if (!empty($errors)) {
        $errorMessage = implode("\n", $errors);
        r2(U . 'plugin/leads', 'w', "Imported $leadCount leads with some errors: $errorMessage");
      } else {
        r2(U . 'plugin/leads', 's', "Successfully imported $leadCount leads");
      }
      
      exit;
    } else {
      r2(U . 'plugin/leads&action=import', 'e', 'Could not open file');
      exit;
    }
  }
  
  $ui->display('lead-import.tpl');
}

function ExportLeads()
{
  _admin();
  // Get filter parameters
  $status = isset($_GET['status']) ? $_GET['status'] : '';
  $source = isset($_GET['source']) ? $_GET['source'] : '';
  $search = isset($_GET['search']) ? $_GET['search'] : '';
  
  // Build query
  $query = ORM::for_table('tbl_leads');
  
  if (!empty($status)) {
    $query->where('status', $status);
  }
  
  if (!empty($source)) {
    $query->where('source', $source);
  }
  
  if (!empty($search)) {
    $query->where_raw('(name LIKE ? OR phone LIKE ? OR email LIKE ?)', 
      array("%$search%", "%$search%", "%$search%"));
  }
  
  $leads = $query->order_by_desc('created_at')->find_many();
  
  // Set headers for CSV download
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.csv"');
  
  $output = fopen('php://output', 'w');
  
  // Write CSV header
  fputcsv($output, ['Name', 'Phone', 'Email', 'Address', 'Source', 'Status', 'Notes', 'Created At', 'Updated At']);
  
  // Write data rows
  foreach ($leads as $lead) {
    fputcsv($output, [
      $lead->name,
      $lead->phone,
      $lead->email,
      $lead->address,
      $lead->source,
      $lead->status,
      $lead->notes,
      $lead->created_at,
      $lead->updated_at
    ]);
  }
  
  fclose($output);
  exit;
}

function createLeadsTableIfNotExists() 
{
  $db = ORM::get_db();
  $tableCheckQuery = "CREATE TABLE IF NOT EXISTS tbl_leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    address TEXT,
    source VARCHAR(100) DEFAULT 'Direct',
    status VARCHAR(50) DEFAULT 'New',
    notes TEXT,
    assigned_to INT DEFAULT 0,
    customer_id INT DEFAULT 0,
    converted_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX (status),
    INDEX (source),
    INDEX (assigned_to),
    INDEX (customer_id)
  )";
  $db->exec($tableCheckQuery);
  
  // Check if the table exixst or not 
  $countQuery = "SELECT COUNT(*) as count FROM tbl_leads";
  $result = $db->query($countQuery);
  $row = $result->fetch(\PDO::FETCH_ASSOC);
  
  if ($row['count'] == 0) {
    // Yah this will be  issue 
    $configs = [
      ['lead_statuses', 'New,Active,Contacted,Qualified,Proposal,Negotiation,Converted,Lost'],
      ['lead_sources', 'Direct,Referral,Website,Phone,Email,Social Media,Advertisement,Event,Other']
    ];
    
    foreach ($configs as $config) {
      $existing = ORM::for_table('tbl_appconfig')
        ->where('setting', $config[0])
        ->find_one();
        
      if (!$existing) {
        $newConfig = ORM::for_table('tbl_appconfig')->create();
        $newConfig->setting = $config[0];
        $newConfig->value = $config[1];
        $newConfig->save();
      }
    }
  }
}

// Create tables when the script is loaded
createLeadsTableIfNotExists();