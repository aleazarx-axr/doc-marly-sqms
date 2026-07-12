<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Doc Marly SQMS'; ?></title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 0; display: flex; height: 100vh; }
        .sidebar { width: 250px; background-color: #f4f4f4; border-right: 1px solid #ccc; padding: 20px; overflow-y: auto; }
        .sidebar ul { list-style-type: none; padding: 0; }
        .sidebar ul li { padding: 10px 0; border-bottom: 1px solid #ddd; }
        .sidebar ul li a { text-decoration: none; color: #333; }
        .main-content { flex: 1; padding: 20px; overflow-y: auto; position: relative; }
        
        /* Dashboard specific */
        .header-section { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
        .dashboard-grid { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
        .card { border: 1px solid #ccc; padding: 20px; border-radius: 5px; background: #fafafa; flex: 1; min-width: 200px; }
        .card h3 { margin-top: 0; font-size: 16px; color: #555; }
        .card .value { font-size: 24px; font-weight: bold; margin: 10px 0; }
        
        /* Tables and Forms */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        .btn { padding: 8px 15px; cursor: pointer; background-color: #333; color: #fff; border: none; border-radius: 3px; }
        
        /* Modals */
        .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 40%; border-radius: 5px; }
        .close-btn { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover { color: black; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input[type="text"], .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .checkbox-group { margin-bottom: 10px; display: flex; align-items: center; gap: 5px; }
    </style>
</head>
<body>
