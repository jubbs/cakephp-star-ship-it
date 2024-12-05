<?php

declare(strict_types=1);

namespace StarShipIt;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\RouteBuilder;
use Cake\Core\Configure;
use Cake\Http\Middleware\CsrfProtectionMiddleware;

/**
 * Plugin for StarShipIt
 */
class StarShipItPlugin extends BasePlugin
{
    /**
     * Load all the plugin configuration and bootstrap logic.
     *
     * The host application is provided as an argument. This allows you to load
     * additional plugin dependencies, or attach events.
     *
     * @param \Cake\Core\PluginApplicationInterface $app The host application
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        $key_exists = Configure::check('StarShipIt.API_KEY');
        $subkey_exists = Configure::check('StarShipIt.SUBSCRIPTION_KEY');
        if (!$key_exists || !$subkey_exists) {
            exit("You must set the StarShipIt,API_KEY and StarShipIt.SUBSCRIPTION_KEY in the config file\n");
        }
    }

    /**
     * Add routes for the plugin.
     *
     * If your plugin has many routes and you would like to isolate them into a separate file,
     * you can create `$plugin/config/routes.php` and delete this method.
     *
     * @param \Cake\Routing\RouteBuilder $routes The route builder to update.
     * @return void
     */
    public function routes(RouteBuilder $routes): void
    {
        $routes->plugin(
            'StarShipIt',
            ['path' => '/star-ship-it'],
            function (RouteBuilder $builder) {
                // Add custom routes here
                $builder->connect('/event', ['controller' => 'ShippingLog', 'action' => 'event']);
                $builder->fallbacks();
            }
        );
        parent::routes($routes);
    }

    /**
     * Add middleware for the plugin.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to update.
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        // Add your middlewares here
        // $csrf = new CsrfProtectionMiddleware();

        // // Token check will be skipped when callback returns `true`.
        // $csrf->skipCheckCallback(function ($request) {
        //     // Skip token check for API URLs.
        //     if ($request->getParam('plugin') === 'StarShipIt') {
        //         return true;
        //     }
        // });

        // // Ensure routing middleware is added to the queue before CSRF protection middleware.
        // $middlewareQueue->add($csrf);

        return $middlewareQueue;
    }

    /**
     * Add commands for the plugin.
     *
     * @param \Cake\Console\CommandCollection $commands The command collection to update.
     * @return \Cake\Console\CommandCollection
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        // Add your commands here

        $commands = parent::console($commands);

        return $commands;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
        // Add your services here
    }
}
