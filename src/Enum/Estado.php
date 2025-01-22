<?php

namespace App\Enum;

enum Estado:string
{
    case ENTREGADO = 'Entregado';
    case CANCELADO = 'Cancelado';
    case EN_CURSO = 'En_curso';
}
