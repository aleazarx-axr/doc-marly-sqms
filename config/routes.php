<?php
return [
    '/' => 'views/auth/login.php',
    '/login' => 'AuthController@processLogin',
    '/logout' => 'AuthController@logout',
    
    '/admin/dashboard' => 'views/admin/dashboard.php',
    '/admin/services' => 'views/admin/settings.php',
    '/admin/requirements' => 'views/admin/settings.php',
    '/admin/sites' => 'views/admin/settings.php',
    '/admin/service_assignments' => 'views/admin/settings.php',
    '/admin/settings' => 'views/admin/settings.php',
    
    '/user/dashboard' => 'views/user/dashboard.php',
    
    // API endpoints mapping to controllers
    '/api/services/add' => 'controllers/ServiceController.php?action=add',
    '/api/services/edit' => 'controllers/ServiceController.php?action=edit',
    '/api/services/archive' => 'controllers/ServiceController.php?action=archive',
    
    '/api/service_steps/save' => 'controllers/ServiceStepController.php?action=save',
    '/api/service_steps/get' => 'controllers/ServiceStepController.php?action=get',
    
    '/api/service_assignments/save' => 'controllers/SiteServiceController.php?action=save',
    '/api/service_assignments/get' => 'controllers/SiteServiceController.php?action=get',
    
    '/api/sites/add' => 'controllers/SiteController.php?action=add',
    '/api/sites/edit' => 'controllers/SiteController.php?action=edit',
    '/api/sites/archive' => 'controllers/SiteController.php?action=archive',
    
    '/api/counters/add' => 'controllers/CounterController.php?action=add',
    '/api/counters/edit' => 'controllers/CounterController.php?action=edit',
    '/api/counters/archive' => 'controllers/CounterController.php?action=archive',
    
    '/api/counter_assignments/save' => 'controllers/CounterServiceController.php?action=save',
    '/api/counter_assignments/get' => 'controllers/CounterServiceController.php?action=get',
    
    '/api/requirements/add' => 'RequirementController@add',
    '/api/requirements/archive' => 'RequirementController@archive',
];
?>
