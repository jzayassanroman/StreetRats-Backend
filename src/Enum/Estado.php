<?php

namespace App\Enum;

enum Estado: string {
    case ENTREGADO = 'entregado';
    case CANCELADO = 'cancelado';
    case EN_CURSO = 'en_curso';
    case ENVIADO = 'enviado';
}
