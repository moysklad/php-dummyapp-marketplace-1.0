<?php

require_once __DIR__ . '/../lib/lib.php';

function processDocumentButtonClick(string $buttonName, string $extensionPoint, string $objectId, mixed $user): array
{
    $role = is_object($user) ? ($user->role ?? 'unknown') : 'unknown';

    if ($buttonName === 'show-notification') {
        return [
            'action' => 'showNotification',
            'params' => [
                'text' => "Кнопка нажата в '$extensionPoint' для объекта с ИД '$objectId' пользователем с ролью $role"
            ]
        ];
    } elseif ($buttonName === 'navigate-to') {
        return [
            'action' => 'navigateTo',
            'params' => [
                'url' => 'https://api.whatsapp.com/send/?phone=%2B79127775533'
            ]
        ];
    } elseif ($buttonName === 'show-popup') {
        return [
            'action' => 'showPopup',
            'params' => [
                'popupName' => 'some-popup',
                'popupParameters' => ['paramStr' => 'Hello', 'paramInt' => 777]
            ]
        ];
    }

    return [];
}

function processListButtonClick(string $buttonName, string $extensionPoint, iterable $objects): array
{
    if ($buttonName === 'show-notification') {
        $items = [];

        foreach ($objects as $item) {
            if (is_object($item) && isset($item->id)) {
                $items[] = "'$item->id'";
            }
        }

        return [
            'action' => 'showNotification',
            'params' => [
                'text' => "Кнопка нажата в '$extensionPoint' для объектов " . implode(', ', $items)
            ]
        ];
    }

    return [];
}
