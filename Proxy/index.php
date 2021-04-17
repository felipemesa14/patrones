<?php
/*
* En este ejemplo vamos a mostrar como recuperar datos de un la copia de un objeto inicial.
 * Para lo cual tendremos 2 metodos que nos retornaran el objeto inicial de listado de paises y objeto inicial del pais.
 * Luego crearemos un proxy que permitira realizar la validación si ya existe en la cache una instancia de ambos objetos para retornar la copia del objeto inicial
 *
 * La caracteristica de calidad que ofrece este patron es eficiencia de desempeño.
 */


interface Countries
{
    public function allCountries();

    public function getCountry($code);
}

/**
 * Servicio que contiene listado de ciudades o ciudad por codigo
 * Class SimpleCountries
 */
class SimpleCountries implements Countries
{
    public function allCountries()
    {

        $result = file_get_contents("https://restcountries.eu/rest/v2/all");

        return $result;
    }

    public function getCountry($code)
    {
        $result = file_get_contents("https://restcountries.eu/rest/v2/alpha/{$code}");

        return $result;

    }

}

/**
 * cache o proxy antes de consumir servicio inicial de ciudades.
 * Class CacheCountries
 */
class CacheCountries implements Countries
{
    /**
     * @var SimpleCountries
     */
    private $countries;

    /**
     * @var array
     */
    private $cacheAllCountries = [];

    /**
     * @var array
     */
    private $cacheCountry = [];

    public function __construct(SimpleCountries $countries)
    {
        $this->countries = $countries;
    }

    public function allCountries()
    {
        if (!$this->cacheAllCountries) {
            $result = $this->countries->allCountries();
            $this->cacheAllCountries = $result;
        } else {
            echo "(Reutilizando objeto inicial)";
        }

        return $this->cacheAllCountries;

    }

    public function getCountry($code)
    {
        if (!isset($this->cacheCountry[$code])) {
            $result = $this->countries->getCountry($code);
            $this->cacheCountry[$code] = $result;
        } else {
            echo "(Reutilizando objeto inicial de ciudad)";
        }

        return $this->cacheCountry[$code];

    }
}

function getCountriesClient(Countries $countries)
{

    $result = $countries->allCountries();

    //Nueva ejecución para verificar que cuando se instancia el servicio de cache si se esta imprimiendo el mensaje y no consumiendo nuevamente el servicio
    $countries->allCountries();

    return $result;

}

function getCountryClient(Countries $countries, $code)
{

    $result = $countries->getCountry($code);

    //Nueva ejecución para verificar que cuando se instancia el servicio de cache si se esta imprimiendo el mensaje y no consumiendo nuevamente el servicio y retornando la copia del objeto inicial
    $countries->getCountry($code);

    return $result;

}

//Implementación de consumo directo del servicio
echo "Consultando paises del servicio real. ";
$realCountries = new SimpleCountries();
var_dump(getCountriesClient($realCountries));

echo "\n";

//Implementación de consumo con cache del listado de ciudades
echo "Consultando paises de la cache. ";
$cacheCountries = new CacheCountries($realCountries);
var_dump(getCountriesClient($cacheCountries));

echo "\n";

//Implementación de consumo directo del servicio
echo "Consultando pais colombia del servicio real. ";
var_dump(getCountryClient($realCountries, "co"));

echo "\n";

//Implementación de consumo con cache del pais colombia
echo "Consultando pais colombia de la cache. ";
var_dump(getCountryClient($cacheCountries, "co"));
