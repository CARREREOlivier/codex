<?php

namespace App\Enum;

enum ArticleStatus: string
{
    case BROUILLON = 'brouillon';
    case PUBLIE = 'publié';
}
