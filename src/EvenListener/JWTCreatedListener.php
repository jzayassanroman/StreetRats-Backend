<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function TokenIdMetido(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        // Agregamos el ID del usuario al payload del token
        $payload = $event->getData();
        $payload['id'] = $user->getId();

        $event->setData($payload);
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        // Verifica si el usuario es válido
        if (!$user instanceof UserInterface) {
            return;
        }

        $roles = $user->getRoles();
        $payload = $event->getData();
        $payload['rol'] = $roles[0] ?? 'User'; // Asegura que siempre haya un rol

        // Buscar el Cliente asociado al usuario
        $cliente = $user->getCliente(); // 🔹 Asegúrate de que el método `getCliente()` existe en `User`

        if ($cliente) {
            $payload['id_cliente'] = $cliente->getId(); // Agregar ID del cliente al token
        }

        $event->setData($payload);
    }




}