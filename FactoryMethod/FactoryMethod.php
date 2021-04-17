<?php

include "PaymentMethods/Pse/Pse.php";
include "PaymentMethods/Amex/Amex.php";
include "PaymentMethods/Visa/Visa.php";

class FactoryMethod
{

    public function create($typeMethod)
    {

        $paymentMethods = [
            "pse" => "Pse",
            "visa" => "Visa",
            "amex" => "Amex"
        ];

        if (!array_key_exists($typeMethod, $paymentMethods)) {
            throw new Exception("Report {$typeMethod} does not exist");
        }

        $paymentMethod = "PaymentMethods/{$paymentMethods[$typeMethod]}/{$paymentMethods[$typeMethod]}.php";
        if (!file_exists($paymentMethod)) {
            throw new Exception("Class {$paymentMethod} does not exist");
        }

        return new $paymentMethods[$typeMethod]();
    }

}