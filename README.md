# symfony-rest-api
Symfony one file simple rest app with micro kernel.

### How to run it

`cd xampp/htdocs` for windows

`git clone https://github.com/maciejkosiarski/symfony-rest-api.git`

`cd symfony-rest-api`

`composer install`

Create new database and import table from `schema.sql`

Configure doctrine in `configureContainer` method:

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
        
Your api is now available at http://localhost/symfony-rest-api/index.php/api/article

### The api will respond to

`GET    ->   api/article`

`GET    ->   api/article/{id}`

`POST   ->   api/article`

`PUT    ->   api/article/{id}`

`DELETE ->   api/article/{id}`

### Authentication

user: admin

pass: secretpass

Configure security in `configureContainer` method:

        $c->loadFromExtension('security', [
            'providers' => [
                'in_memory' => [
                    'memory' => [
                        'users' => [
                            'admin' => [
                                'password' => 'secretpass',
                                'roles' => 'ROLE_ADMIN'
                            ]
                        ]
                    ],
                ],
            ],
            ...
