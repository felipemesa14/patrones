<?php
include "FactoryMethod.php";

/*
 * En este ejemplo vamos a mostrar que el cliente seleccino un medio de pago en especifico y según ese medio de pago
 * Nos iremos a realizar un flujo diferente de integración de pago, para esto con el factory method, instanciaremos objetos que contendran estas funcionalidades.
 * Solo imprimiremos el method de pago seleccionado.
 *
 * La caracteristica de calidad que ofrece este patron es la compatiblidad.
 */
try {

    echo "\nEnter your payment Method:\n";
    $typeMethod  = readline();

    if ($typeMethod == "pse" || $typeMethod == "amex" || $typeMethod == "visa") {
        $paymentMethod = FactoryMethod::create($typeMethod);
        echo $paymentMethod->getMethodPayment();
    } else {
        throw new Exception("The Payment method {$typeMethod} not valid.");
    }

} catch (Exception $exception) {

    echo $exception->getMessage();

}

