<?php

$pdo = new PDO('mysql:host=localhost;dbname=test_task', 'root', '99145673ffF');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


$selectedGroupId = isset($_GET['groupId']) ? $_GET['groupId'] : 0;

// Функция для получения количества товаров в группе и всех ее подгруппах
function getProductsCount($pdo, $groupId) {
    // Получаем список подгрупп группы
    $stmt = $pdo->prepare('SELECT id FROM test_task.groups WHERE id_parent = :id');
    $stmt->execute(['id' => $groupId]);
    $subgroups = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Добавляем id группы в список подгрупп
    $subgroups[] = $groupId;

    // Составляем список id всех товаров в группе и ее подгруппах
    $inClause = implode(',', array_fill(0, count($subgroups), '?'));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_task.products WHERE id_group IN ($inClause)");
    $stmt->execute($subgroups);
    return $stmt->fetchColumn();
}

// Функция для вывода списка групп
function printGroups($pdo, $parentId, $level = 0) {
    global $selectedGroupId;
    // Получаем список групп с указанным id_parent
    $stmt = $pdo->prepare('SELECT * FROM test_task.groups WHERE id_parent = :id');
    $stmt->execute(['id' => $parentId]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Выводим список групп
    echo "<ul>";
    foreach ($groups as $group) {
        // Выводим название группы и количество товаров в ней и ее подгруппах
        $productsCount = getProductsCount($pdo, $group['id']);
        echo "<li>";
        echo str_repeat("&nbsp;", $level * 4); // Отступ для вложенных групп
        echo "<a href=\"?groupId={$group['id']}\">{$group['name']}</a> ({$productsCount})";

        // Если группа выбрана, выводим список ее подгрупп и товаров
        if ($selectedGroupId == $group['id']) {
            printGroups($pdo, $group['id'], $level + 1);
            printProducts($pdo, $group['id']);
        }

        echo "</li>";
    }
    echo "</ul>";
}

// Функция для вывода списка товаров в группе
function printProducts($pdo, $groupId) {
    // Получаем список всех товаров в группе и ее подгруппах
    $stmt = $pdo->prepare('SELECT * FROM test_task.products WHERE id_group IN (SELECT id FROM test_task.groups WHERE id = :id OR id_parent = :id)');
    $stmt->execute(['id' => $groupId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Выводим список товаров
    echo "<ul>";
    foreach ($products as $product) {
        echo "<li>{$product['name']}</li>";
    }
    echo "</ul>";
}

// Выводим список групп первого уровня и все товары
printGroups($pdo, 0);
printProducts($pdo, 0);

