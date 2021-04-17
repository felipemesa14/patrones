<?php

/*
 * EN este ejemplo realizaremos un patron de diseño para delegar responsabilidades a diferentes methodos de comprobación,
 * Dependiendo de la validación se pasa la validación de usuario o contraseña a el siguiente metodo responsable.
 * Al final si los metodos anteriores pasaron, se realizara una ultima comprobación del rol de los usuarios registrados.
 *
 * La caracteristica de calidad que ofrece este patron es de seguridad.
 */

abstract class Middleware
{
    private $next;

    public function linkWith(Middleware $next)
    {
        $this->next = $next;

        return $next;
    }

    public function check($email, $password)
    {
        if (!$this->next) {
            return true;
        }

        return $this->next->check($email, $password);
    }
}

class UserExistsMiddleware extends Middleware
{
    private $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function check($email, $password)
    {
        if (!$this->server->hasEmail($email)) {
            echo "El email no esta registrado!\n";

            return false;
        }

        if (!$this->server->isValidPassword($email, $password)) {
            echo "Contraseña invalida!\n";

            return false;
        }

        return parent::check($email, $password);
    }
}

class RoleCheckMiddleware extends Middleware
{
    public function check($email, $password)
    {
        if ($email === "admin@prueba.com") {
            echo "Hola, Administrador!\n";

            return true;
        }
        echo "Hola, usuario!\n";

        return parent::check($email, $password);
    }
}

class ValidationMiddleware extends Middleware
{
    private $requestPerMinute;

    private $request;

    private $currentTime;

    public function __construct($requestPerMinute)
    {
        $this->requestPerMinute = $requestPerMinute;
        $this->currentTime = time();
    }

    public function check($email, $password)
    {
        if (time() > $this->currentTime + 60) {
            $this->request = 0;
            $this->currentTime = time();
        }

        $this->request++;

        if ($this->request > $this->requestPerMinute) {
            echo "Limite de peticiones permitidas! para la autenticación, por favor vuelve a intentarlo\n";
            die();
        }

        return parent::check($email, $password);
    }
}

class Server
{
    private $users = [];


    private $middleware;


    public function setMiddleware(Middleware $middleware)
    {
        $this->middleware = $middleware;
    }


    public function logIn($email, $password)
    {
        if ($this->middleware->check($email, $password)) {
            echo "Autenticación satisfactoria!\n";

            return true;
        }

        return false;
    }

    public function register($email, $password)
    {
        $this->users[$email] = $password;
    }

    public function hasEmail($email)
    {
        return isset($this->users[$email]);
    }

    public function isValidPassword($email, $password)
    {
        return $this->users[$email] === $password;
    }
}


$server = new Server();
$server->register("admin@prueba.com", "admin");
$server->register("user@prueba.com", "user");


$middleware = new ValidationMiddleware(3);
$middleware->linkWith(new UserExistsMiddleware($server))->linkWith(new RoleCheckMiddleware());

$server->setMiddleware($middleware);

do {
    echo "\nIngresa tu email:\n";
    $email = readline();
    echo "Ingresa tu contraseña:\n";
    $password = readline();
    $success = $server->logIn($email, $password);
} while (!$success);
