<?php
namespace GintonicCMS\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Composer\Console\Application as Composer;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Update shell command.
 */
class UpdateShell extends Shell
{
    /**
     * main() method.
     *
     * @return bool|int Success or error code.
     */
    public function main() 
    {
        $connections = ConnectionManager::configured();
        if (empty($connections)) {
            $this->out('Your database configuration was not found.');
            $this->out('Add your database connection information to config/app.php.');
            return false;
        }

        $this->composer('update');

        $this->migrate();
        $this->migrate('Users');
        $this->migrate('Payments');
        $this->migrate('Messages');
        $this->migrate('Acl');

        $this->permissions();
    }

    public function composer($command)
    {
        $input = new ArrayInput(['command' => $command]);
        $application = new Composer();
        $application->setAutoExit(false);
        $application->run($input);
    }

    public function migrate($plugin = null)
    {
        $plugin = ($plugin === null)? '' : ' -p ' . $plugin;
        $this->dispatchShell('GintonicCMS.migrations migrate' . $plugin);
    }

    public function permissions()
    {
        Configure::write('Acl.classname', 'Acl\Adapter\DbAcl');
        $this->dispatchShell('acl_extras aco_sync');
    }
}
