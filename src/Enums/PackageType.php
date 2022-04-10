<?php

namespace LifeSpikes\MonorepoCLI\Enums;

enum PackageType: string
{
    case COMPOSER = 'composer';
    case NODE = 'node';
}
