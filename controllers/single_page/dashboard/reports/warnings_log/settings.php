<?php
namespace Concrete\Package\WarningsLog\Controller\SinglePage\Dashboard\Reports\WarningsLog;

use Concrete\Core\Page\Controller\DashboardSitePageController;
use Concrete\Package\WarningsLog\Src\Configuration;
use Application\Concrete\Error\Provider\WhoopsServiceProvider;

defined('C5_EXECUTE') or die('Access Denied.');

class Settings extends DashboardSitePageController
{
    public function view()
    {
        $config = $this->app->make('config');
        $this->set('default_provider', Configuration::getDefaultProvider());
        $this->set('warningslog_provider', WhoopsServiceProvider::class);
        $this->set('current_provider', $config->get(Configuration::FULL_KEY));
    }

    public function update_settings()
    {
        if ($this->token->validate('update_settings')) {
            if ($this->isPost()) {
                $config = $this->app->make('config');
                $current_provider = $config->get(Configuration::FULL_KEY);
                switch ($this->post('errorhandler')) {
                    case 'default':
                        $new_provider = Configuration::getDefaultProvider();
                        break;
                    case 'warningslog':
                        $new_provider = WhoopsServiceProvider::class;
                        break;
                    default:
                        $new_provider = $current_provider;
                        break;
                }
                $msg = t('Error Handling configured successfully.');
                $loc = \Localization::getInstance();
                $loc->pushActiveContext('system');
                if ($current_provider !== $new_provider) {
                    $config->save(Configuration::FULL_KEY, $new_provider);
                }
                $loc->popActiveContext();
                $this->flash('success', $msg);
                $this->redirect('/dashboard/reports/warnings_log/settings');
            }
        } else {
            $this->set('error', [$this->token->getErrorMessage()]);
            $this->view();
        }
    }
}
