<?php

require_once 'lib.php';

function processDocumentButtonClick(string $buttonName, string $extensionPoint, string $objectId, $user)
{
    if ($buttonName == 'show-notification') {
        return [
            'action' => 'showNotification',
            'params' => [
                'text' => "Кнопка нажата в '$extensionPoint' для объекта с ИД '$objectId' пользователем с ролью $user->role"
            ]
        ];
    } elseif ($buttonName == 'navigate-to') {
        return [
            'action' => 'navigateTo',
            'params' => [
                'url' => 'https://api.whatsapp.com/send/?phone=%2B79127775533'
            ]
        ];
    }
}

function processListButtonClick(string $buttonName, string $extensionPoint, $objects, $user)
{
    if ($buttonName == 'show-notification') {
        $items = '';
        foreach ($objects as $item) {
            $items .= "'$item->id', ";
        }

        return [
            'action' => 'showNotification',
            'params' => [
                'text' => "Кнопка нажата в '$extensionPoint' для объектов $items"
            ]
        ];
    }
}
