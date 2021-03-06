<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit830044aeb5f2735769faabb084288cb1
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'Vikas5914\\' => 10,
        ),
        'S' => 
        array (
            'Sample\\' => 7,
        ),
        'P' => 
        array (
            'PayPalHttp\\' => 11,
            'PayPalCheckoutSdk\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Vikas5914\\' => 
        array (
            0 => __DIR__ . '/..' . '/vikas5914/steam-auth/src',
        ),
        'Sample\\' => 
        array (
            0 => __DIR__ . '/..' . '/paypal/paypal-checkout-sdk/samples',
        ),
        'PayPalHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/paypal/paypalhttp/lib/PayPalHttp',
        ),
        'PayPalCheckoutSdk\\' => 
        array (
            0 => __DIR__ . '/..' . '/paypal/paypal-checkout-sdk/lib/PayPalCheckoutSdk',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit830044aeb5f2735769faabb084288cb1::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit830044aeb5f2735769faabb084288cb1::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit830044aeb5f2735769faabb084288cb1::$classMap;

        }, null, ClassLoader::class);
    }
}
