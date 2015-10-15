<?php
namespace SkelletonApplication;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use SkelletonApplication\Listener\UserListener;
use Zend\View\Helper\Navigation;

class Module
{
	public function onBootstrap(MvcEvent $e){
		$app = $e->getApplication();
		$eventManager = $app->getEventManager();
		$sm = $app->getServiceManager();

		// Attach UserListener for role and UserProfile handling
		$listener = $sm->get(UserListener::class);
		$eventManager->attach($listener);

		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		$eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'initTranslator'));
		
		// Enable BjyAuthorize when not in console mode
		if(!\Zend\Console\Console::isConsole()) {
			// Add ACL information to the Navigation view helper
			$authorize = $sm->get('BjyAuthorizeServiceAuthorize');
			$acl = $authorize->getAcl();
			$role = $authorize->getIdentity();
			Navigation::setDefaultAcl($acl);
			Navigation::setDefaultRole($role);		
		}
		
		if($e->getRouter() instanceof \Zend\Mvc\Router\Http\TranslatorAwareTreeRouteStack){
			/* @var $translator \Zend\I18n\Translator\Translator */
			$translator = $e->getApplication()->getServiceManager()->get('translator');
			$e->getRouter()->setTranslator($translator);
		}
	}
	
	public function initTranslator(MvcEvent $e){
		$languages = array(
			'de' => 'de_DE',
			'en' => 'en_US'
		);
		
		$routeMatch = $e->getRouteMatch();
		if(!$routeMatch){
			return;
		}
		/* @var $translator \Zend\I18n\Translator\Translator */
		$translator = $e->getApplication()->getServiceManager()->get('translator');
		
		// add Db Loader factory
		$translator->getPluginManager()->setFactory(I18n\Translator\Loader\Db::class, I18n\Translator\Loader\Factory\DbFactory::class);
		
		$lang = $routeMatch->getParam('locale');
		if(!$lang || !in_array($lang, $languages)){
			return;
		}
		$translator->setLocale($lang);
	}

	public function getConfig(){
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig(){
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
		);
	}

	public function getServiceConfig(){
		return array(
			'invokables' => array(
			),
			'factories' => array(
			),
		);
	}
}
