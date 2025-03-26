<?php

function validateMenuType(string $type) : bool
{
    $menuTypes = [
        'header',
        'footer'
    ];
    return in_array($type, $menuTypes);
}

function getMenuData(string $type) : array
{
  $menu = [];
  if (validateMenuType($type)) {
      if ($type === 'header') {
          $menu = [
              'home' => [
                  'name' => 'Domov',
                  'path' => 'index.php',
              ],
              'shows' => [
                  'name' => 'Predstavenia',
                  'path' => 'predstavenia.php',
              ],
              'contact' => [
                  'name' => 'Kontakt',
                  'path' => 'contact.php',
              ]
              ];
      }
  }
  return $menu;
}

function printMenu(array $menu)
{
    foreach ($menu as $menuName => $menuData) {
        echo '<li><a href="'.$menuData['path'].'">'.$menuData['name'].'</a></li>';
    }
}