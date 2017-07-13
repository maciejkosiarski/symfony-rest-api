<?php

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use MicroApp\Entity\Article;

// require Composer's autoloader
require __DIR__.'/vendor/autoload.php';

class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle()
        ];
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'secret' => 'S0ME_SECRET'
        ]);
        
        $c->loadFromExtension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_mysql',
                'host' => '127.0.0.1',
                'port' => null,
                'dbname' => 'symfony-micro',
                'user' => 'root',
                'password' => '',
                'charset' => 'UTF8'
            ],
            'orm' => [
                'auto_generate_proxy_classes' => false,
                'auto_mapping' => true,
                'mappings' => [
                    'App' => [
                        'is_bundle' => false,
                        'type' => 'annotation',
                        'dir' => Kernel::getRootDir().'/src/MicroApp/Entity',
                        'prefix' => 'MicroApp\Entity\\'
                    ]
                ]
            ]
        ]);

    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->add('/test', 'kernel:testAction', 'test');
    }
    
    public function testAction()
    {
        
        $articles = $this->container->get('doctrine')->getRepository(Article::class)->findAll();
        echo '<pre>';
        print_r($articles);
        $response = new Response(
            '<h1>TEST</h2>',
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
        
        return $response;
    }
    
}

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
