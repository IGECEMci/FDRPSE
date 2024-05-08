<?php 

use CodelyTv\CodingStyle;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

// Es un formateadir de código regido por PSR12 ./vendor/bin/ecs check
// El formateador asigna tipado estricto para todo el código dentro de la carptea /app

return function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([__DIR__ . '/app',]);

    $ecsConfig->sets([CodingStyle::ALIGNED]);
    // Or this if you prefer to have the code aligned
    // $ecsConfig->sets([CodingStyle::ALIGNED]);
};
