<?php

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

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
                'dbname' => 'your_dbname',
                'user' => 'your_user',
                'password' => 'your_pass',
                'charset' => 'UTF8'
            ]
        ]);
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->add('/api/article', 'kernel:indexAction', 'api_article_index')
            ->setMethods('GET');
        $routes->add('/api/article/{id}', 'kernel:showAction', 'api_article_show')
            ->setMethods('GET');
        $routes->add('/api/article', 'kernel:newAction', 'api_article_new')
            ->setMethods('POST');
        $routes->add('/api/article/{id}', 'kernel:editAction', 'api_article_edit')
            ->setMethods('PUT');
        $routes->add('/api/article/{id}', 'kernel:deleteAction', 'api_article_delete')
            ->setMethods('DELETE');
    }
    
    public function indexAction()
    {
        try {
            $articles = $this->container->get('doctrine.dbal.default_connection')
                ->fetchAll('SELECT * FROM article');
        }
        catch (Doctrine\DBAL\DBALException $e) {
                return new JsonResponse(null, 400);
        }
        
        return new JsonResponse($articles);
    }
    
    public function showAction($id)
    {
        try {
            $article = $this->container->get('doctrine.dbal.default_connection')
                ->fetchAll('SELECT * FROM article WHERE id = '.$id);
        }
        catch (Doctrine\DBAL\DBALException $e) {
                return new JsonResponse(null, 400);
        } 
        
        return new JsonResponse($article);
    }
    
    public function newAction(Request $request)
    {   
        $currentDate = new \DateTime('now');
        
        $data = [
            $request->request->get('title'),
            $request->request->get('content'),
            $request->request->get('quantity'),
            $currentDate->format('Y-m-d H:i:s')
        ];
        try {
            $conn = $this->container->get('doctrine.dbal.default_connection');

            $stmt = $conn->prepare('INSERT INTO article (title, content, quantity, created_at) VALUES (?, ?, ?, ?)');
            
            if($stmt->execute($data)){
                return new JsonResponse(null, 201);
            }
        }
        catch (Doctrine\DBAL\DBALException $e) {
                return new JsonResponse(null, 400);
        } 

        return new JsonResponse(null, 400);
    }
    
    public function editAction(Request $request, $id)
    {   
        try {
            $conn = $this->container->get('doctrine.dbal.default_connection');

            $stmt = $conn->prepare('UPDATE article SET title = :title, content = :content, quantity = :quantity WHERE id = :id');
            $stmt->bindParam(':title',$request->request->get('title'), PDO::PARAM_STR);
            $stmt->bindParam(':content',$request->request->get('content'), PDO::PARAM_STR);
            $stmt->bindParam(':quantity',$request->request->get('quantity'), PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if($stmt->execute()){
                return new JsonResponse(null, 200);
            }
        }
        catch (Doctrine\DBAL\DBALException $e) {
                return new JsonResponse(null, 400);
        } 
        
        return new JsonResponse(null, 400);
    }
    
    public function deleteAction($id)
    {
        try {
            $conn = $this->container->get('doctrine.dbal.default_connection');

            $stmt = $conn->prepare('DELETE FROM article WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if($stmt->execute()){
                return new JsonResponse(null, 200);
            }
        }
        catch (Doctrine\DBAL\DBALException $e) {
                return new JsonResponse(null, 400);
        } 
        
        return new JsonResponse(null, 400);
    }
    
}

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
