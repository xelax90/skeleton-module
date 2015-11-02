<?php
namespace SkelletonApplication;

use BjyAuthorize\Provider;
use BjyAuthorize\Guard;
use XelaxAdmin\Controller\ListController;

$xelaxConfig = array(
	'list_controller' => array(
		'userprofile' => array(
			'name' => 'UserProfile', 
			'controller_class' => ListController::class, 
			'base_namespace' => __NAMESPACE__,
			'list_columns' => array(gettext_noop('Id') => 'userId', gettext_noop('Name') => 'displayName'),
			'list_title' => gettext_noop('User Profiles'),
			'create_route' => array(
				'disabled' => true
			),
			'delete_route' => array(
				'disabled' => true
			),
			'route_base' => 'zfcadmin/userprofile',
			'rest_enabled' => true,
			'id_name' => 'userId',
		),
		'user' => array(
			'name' => gettext_noop('User'),
			'controller_class' => Controller\UserController::class, 
			'base_namespace' => __NAMESPACE__,
			'list_columns' => array(gettext_noop('Id') => 'id', gettext_noop('Name') => 'display_name', gettext_noop('E-Mail') => 'email', gettext_noop('State') => 'state'),
			'list_title' => gettext_noop('Users'),
			'route_base' => 'zfcadmin/user',
			'rest_enabled' => true,
			'buttons' => array(
				'block' => array(
					'title' => gettext_noop('Block'),
					'route_builder' => function($view, $id, $alias = "", $item = null){
						$urlHelper = $view->plugin('url');
						$allowHelper = $view->plugin('isAllowed');
						if(!$allowHelper('administration', 'user/block')){
							return false;
						}
						if($item !== null && !$item->isActive()){
							return false;
						}
						return $urlHelper('zfcadmin/user/block', array('userId' => $id));
					}
				),
				'unblock' => array(
					'title' => gettext_noop('Unblock'),
					'route_builder' => function($view, $id, $alias = "", $item = null){
						$urlHelper = $view->plugin('url');
						$allowHelper = $view->plugin('isAllowed');
						if(!$allowHelper('administration', 'user/unblock')){
							return false;
						}
						if($item !== null && $item->isActive()){
							return false;
						}
						return $urlHelper('zfcadmin/user/unblock', array('userId' => $id));
					}
				),
			),
		),
		'role' => array(
			'name' => 'Role', 
			'controller_class' => Controller\RoleController::class, 
			'base_namespace' => __NAMESPACE__,
			'list_columns' => array(gettext_noop('Id') => 'id', gettext_noop('RoleId') => 'roleId'),
			'list_title' => gettext_noop('Roles'),
			'route_base' => 'zfcadmin/roles',
			'rest_enabled' => true,
		),
	),
);

$guardConfig = array(
	['route' => 'zfcuser',                  'roles' => ['guest', 'user'] ],
	['route' => 'zfcuser/login',            'roles' => ['guest', 'user'] ],
	['route' => 'zfcuser/register',         'roles' => ['guest'] ],
	['route' => 'zfcuser/register/registered','roles' => ['guest'] ],
	['route' => 'zfcuser/authenticate',     'roles' => ['guest'] ],
	['route' => 'zfcuser/logout',           'roles' => ['guest', 'user'] ],
	['route' => 'zfcuser/changepassword',   'roles' => ['user'] ],
	['route' => 'zfcuser/changeemail',      'roles' => ['user'] ],
	['route' => 'zfcuser/forgotpassword',   'roles' => ['guest']],
	['route' => 'zfcuser/resetpassword',    'roles' => ['guest']],
	['route' => 'zfcuser/check-token',      'roles' => ['guest']],
	
	['route' => 'home',                     'roles' => ['guest', 'user'] ],

	// modules
	['route' => 'doctrine_orm_module_yuml', 'roles' => ['administrator'] ],

	// admin
	['route' => 'zfcadmin',                      'roles' => ['moderator']],

	// user admin
	['route' => 'zfcadmin/userprofile',          'roles' => ['administrator']],
	['route' => 'zfcadmin/user' ,                'roles' => ['administrator']],
	['route' => 'zfcadmin/user/block' ,          'roles' => ['administrator']],
	['route' => 'zfcadmin/user/unblock' ,        'roles' => ['administrator']],
	['route' => 'zfcadmin/roles',                'roles' => ['administrator']],
	
	// site config
	['route' => 'zfcadmin/siteconfig/registration' ,  'roles' => ['moderator']],
	['route' => 'zfcadmin/siteconfig/emails' ,  'roles' => ['moderator']],
);

$ressources = array(
	'debug',
	'user', // navigation ZfcUser
	'administration', // navigation for administration
);

$ressourceAllowRules = array(
	[['user'],  'user', 'profile'],
	[['user'],  'user', 'logout'],
	[['user'],  'user', 'changepassword'],
	[['guest'], 'user', 'login'],
	[['guest'], 'user', 'register'],

	[['moderator'], 'administration', 'login'],
	[['moderator'], 'administration', 'user/list'],
	[['moderator'], 'administration', 'user/create'],
	[['moderator'], 'administration', 'user/block'],
	[['moderator'], 'administration', 'user/unblock'],
	[['moderator'], 'administration', 'userprofile'],

	[['moderator'], 'debug', 'moderator'],
	[['administrator'], 'debug', 'administrator'],
	
	[['moderator'], 'siteconfig', 'registration/list'],
	[['administrator'], 'siteconfig', 'registration/edit'],
	[['moderator'], 'siteconfig', 'emails/list'],
	[['administrator'], 'siteconfig', 'emails/edit'],
);

return array(
	'controllers' => array(
		'invokables' => array(
			Controller\IndexController::class => Controller\IndexController::class,
			Controller\UserController::class => Controller\UserController::class,
			Controller\RegistrationConfigController::class => Controller\RegistrationConfigController::class,
			Controller\EmailTemplateController::class => Controller\EmailTemplateController::class,
			Controller\RoleController::class => Controller\RoleController::class,
		),
		'factories' => array(
			Controller\FrontendUserController::class => function($controllerManager) {
					/* @var \Zend\Mvc\Controller\ControllerManager $controllerManager*/
					$serviceManager = $controllerManager->getServiceLocator();
					/* @var \ZfcUser\Controller\RedirectCallback $redirectCallback */
					$redirectCallback = $serviceManager->get('zfcuser_redirect_callback');
					$controller = new Controller\FrontendUserController($redirectCallback);
					return $controller;
			},
		),
	),
	
	'xelax' => $xelaxConfig,
	
	'router' => array(
		'router_class' => Mvc\Router\Http\LanguageTreeRouteStack::class,
		'routes' => include 'router.config.php',
	),
	
	'bjyauthorize' => array(
		// resource providers provide a list of resources that will be tracked
        // in the ACL. like roles, they can be hierarchical
        'resource_providers' => array(
            Provider\Resource\Config::class => $ressources,
        ),

		
		'rule_providers' => array(
			Provider\Rule\Config::class => array(
                'allow' => $ressourceAllowRules,

                // Don't mix allow/deny rules if you are using role inheritance.
                // There are some weird bugs.
                'deny' => array(
                    // ...
                ),
            )
		),
		
        'guards' => array(
            Guard\Route::class => $guardConfig
		),
	),
	
	'skelleton_application' => array(
		'roles' => array(
			'guest' => array(),
			'user' => array(
				'moderator' => array(
					'administrator' => array() // Admin role must be leaf and must contain 'admin'
				)
			)
		)
	),
	
	'zfctwig' => array(
		'loader_chain' => array(
			Twig\DbLoader::class,
		),
	),
	
    'service_manager' => include 'service.config.php',

	// language options
	'translator' => array(
		'locale' => array('de_DE', 'de_DE'),
		'translation_file_patterns' => array(
			array(
				'type'     => 'gettext',
				'base_dir' => __DIR__ . '/../language',
				'pattern'  => '%s.mo',
			),
			array(
				'type'     => 'gettext',
				'base_dir' => __DIR__ . '/../../../vendor/zf-commons/zfc-user/src/ZfcUser/language',
				'pattern'  => '%s.mo',
			),
		),
		'remote_translation' => array(
			array(
				'type' => I18n\Translator\Loader\Db::class
			)
		)
	),

	// view options
	'view_manager' => array(
		'display_not_found_reason' => true,
		'display_exceptions'       => true,
		'doctype'                  => 'HTML5',
		'not_found_template'       => 'error/404',
		'exception_template'       => 'error/index',
		'template_map' => array(
			'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
			'error/404'               => __DIR__ . '/../view/error/404.phtml',
			'error/index'             => __DIR__ . '/../view/error/index.phtml',
			'zfc-user/user/login'     => __DIR__ . '/../view/zfc-user/user/login.phtml',
			'skelleton-application/frontend-user/register'  => __DIR__ . '/../view/zfc-user/user/register.phtml',
			'skelleton-application/frontend-user/registered'  => __DIR__ . '/../view/zfc-user/user/registered.phtml',
		),
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),

	'view_helpers' => array(
		'invokables' => array(
			'languageSwitch'          => View\Helper\LanguageSwitch::class,
		),
	),
	
	// Site navigation
	'navigation' => array(
		// default navigation
		'default' => array(
			'home'            => array('label' => gettext_noop('Home'),            'route' => 'home'),
			'admin'           => array('label' => gettext_noop('Admin'),           'route' => 'zfcadmin',               'resource' => 'administration', 'privilege' => 'login'),
			'login'           => array('label' => gettext_noop('Login'),           'route' => 'zfcuser/login',          'resource' => 'user',           'privilege' => 'login'),
			'register'        => array('label' => gettext_noop('Register'),        'route' => 'zfcuser/register',       'resource' => 'user',           'privilege' => 'register'),
			'profile'         => array('label' => gettext_noop('Profile'),         'route' => 'zfcuser',                'resource' => 'user',           'privilege' => 'profile'),
			'change-password' => array('label' => gettext_noop('Change Password'), 'route' => 'zfcuser/changepassword', 'resource' => 'user',           'privilege' => 'changepassword'),
			'logout'          => array('label' => gettext_noop('Logout'),          'route' => 'zfcuser/logout',         'resource' => 'user',           'privilege' => 'logout'),
		),
		// admin navigation
		'admin' => array(
			'zfcuseradmin'  => null,
			'home'          => array('label' => gettext_noop('Home'),            'route' => 'home'),
			'config'        => array('label' => gettext_noop('Config'),          'route' => 'zfcadmin/siteconfig/email', 'resource' => 'siteconfig',     'privilege' => 'list', 'pages' => array(
				'email'           => array('label' => gettext_noop('E-Mail'),            'route' => 'zfcadmin/siteconfig/email',        'action' => 'index', 'resource' => 'siteconfig', 'privilege' => 'registration/list'),
				'registration'    => array('label' => gettext_noop('Registration'),      'route' => 'zfcadmin/siteconfig/registration', 'action' => 'index', 'resource' => 'siteconfig', 'privilege' => 'registration/list'),
				'email-templates' => array('label' => gettext_noop('E-Mail Templates'),  'route' => 'zfcadmin/siteconfig/emails',       'action' => 'index', 'resource' => 'siteconfig', 'privilege' => 'emails/list'),
			)),
			'users'         => array('label' => gettext_noop('Users'),           'route' => 'zfcadmin/user',             'resource' => 'administration', 'privilege' => 'user/list' ),
			'user-profiles' => array('label' => gettext_noop('User Profiles'),   'route' => 'zfcadmin/userprofile',      'resource' => 'administration', 'privilege' => 'userprofile')
		),
	),


	// Placeholder for console routes
	'console' => array(
		'router' => array(
			'routes' => array(
			),
		),
	),

	// doctrine config
	'doctrine' => array(
		'driver' => array(
			__NAMESPACE__ . '_driver' => array(
				'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class, // use AnnotationDriver
				'cache' => 'array',
				'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity') // entity path
			),
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
				)
			)
		),

		// Fixtures to create admin user and default roles
		'fixture' => array(
			__NAMESPACE__ . '_fixture' => __DIR__ . '/../data/Fixtures',
		)
	),
);
