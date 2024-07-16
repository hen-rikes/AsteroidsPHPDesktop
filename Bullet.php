<?php

use raylib\Vector2;
use raylib\Color;
use raylib\Rectangle;

class Bullet {
    function __construct(Vector2 $pos, float $angle, float $radius) {
        $this->pos = $pos;
        $this->angle = $angle;
        $this->radius = $radius;
    }

    public $pos;
    public $angle;
    public $radius;
}

function destroy_bullets(&$arry) {
    global $width, $height;

    for ($i = 0; $i < count($arry); $i++) {
        if (!CheckCollisionCircleRec($arry[$i]->pos, $arry[$i]->radius, new Rectangle(0, 0, $width, $height))) {
            array_splice($arry, $i, 1);
        }
    }
}

function move_bullets(&$arry, $angle) {
    foreach ($arry as &$a) {
        $a->pos->x += 650 * cos(d2r($a->angle)) * GetFrameTime();
        $a->pos->y += 650 * sin(d2r($a->angle)) * GetFrameTime();
    }
}

function draw_bullets(&$arry) {
    foreach ($arry as &$a) {
        DrawCircleV($a->pos, $a->radius, Color::WHITE());
    }
}

?>
