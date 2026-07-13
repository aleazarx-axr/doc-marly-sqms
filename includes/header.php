<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Doc Marly SQMS'; ?></title>
    <style>
        body { font-family: sans-serif; font-size: 14px; line-height: 18px; margin: 0; padding: 0; display: flex; height: 100vh; color: #333; }
        
        /* Sidebar */
        .sidebar { width: 250px; background-color: #f4f4f4; border-right: 1px solid #ccc; padding: 15px; overflow-y: auto; font-size: 14px; display: flex; flex-direction: column; }
        .sidebar ul { list-style-type: none; padding: 0; margin: 0; }
        .sidebar ul li { padding: 8px 0; border-bottom: 1px solid #ddd; }
        .sidebar ul li a { text-decoration: none; color: #333; font-size: 14px; }
        
        /* Main Content */
        .main-content { flex: 1; padding: 15px; overflow-y: auto; position: relative; }
        
        /* Dashboard */
        .header-section { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding-bottom: 8px; margin-bottom: 15px; }
        .dashboard-grid { display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap; }
        .card { border: 1px solid #ccc; padding: 15px; border-radius: 8px; background: #fafafa; flex: 1; min-width: 200px; }
        .card h3 { margin-top: 0; font-size: 14px; color: #555; font-weight: 600; }
        .card .value { font-size: 20px; font-weight: bold; margin: 8px 0; }
        
        /* Tables */
        .table-container { border-radius: 8px; border: 1px solid #ccc; overflow: hidden; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { height: 42px; padding: 12px 16px; font-size: 14px; font-weight: 600; text-transform: none; background-color: #f4f4f4; text-align: left; border-bottom: 1px solid #ccc; }
        td { height: 48px; padding: 12px 16px; font-size: 14px; font-weight: 400; vertical-align: middle; border-bottom: 1px solid #eee; }
        table tr:last-child td { border-bottom: none; }
        
        /* Buttons */
        .btn { height: 36px; padding: 0 16px; font-size: 14px; cursor: pointer; background-color: #333; color: #fff; border: none; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; }
        
        /* Forms & Inputs */
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; }
        .form-group input[type="text"], .form-group textarea, input[type="number"], input[type="email"], select { width: 100%; padding: 8px 12px; font-size: 14px; box-sizing: border-box; border-radius: 8px; border: 1px solid #ccc; }
        .checkbox-group { margin-bottom: 8px; display: flex; align-items: center; gap: 5px; font-size: 14px; }
        
        /* Modals */
        .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 40%; border-radius: 8px; }
        .close-btn { color: #aaa; float: right; font-size: 24px; font-weight: bold; cursor: pointer; line-height: 1; }
        .close-btn:hover { color: black; }
        
        /* Icons */
        i, .icon { font-size: 14px; }
    </style>
</head>
<body>
