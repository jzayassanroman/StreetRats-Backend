<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onLexikJwtAuthenticationOnJwtCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        // Verificar si el usuario está verificado
        if (!$user->isVerified()) {
            throw new \Exception('Debes verificar tu email antes de iniciar sesión.');
        }

        // Añadir custom claims al token JWT
        $payload = $event->getData();
        $payload['isVerified'] = $user->isVerified(); // Añadir el estado de verificación al JWT
        $event->setData($payload);
    }
}