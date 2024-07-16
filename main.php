<?php

use raylib\Color;
use raylib\Vector2;
use raylib\Rectangle;
use raylib\Texture;

use const raylib\KeyboardKey\{
    KEY_UP,
    KEY_DOWN,
    KEY_RIGHT,
    KEY_LEFT,
    KEY_W,
    KEY_S,
    KEY_A,
    KEY_D,
    KEY_SPACE
};

include "Asteroid.php";
include "Ship.php";
include "Bullet.php";

$width = 800;
$height = 600;

$asteroids_max_speed = 60;
$asteroids = [];

function d2r(float $degrees) {
    return $degrees * (pi() / 180);
}

InitWindow($width, $height, "Asteroids");

$angle_speed = 250.0;
// TODO: use texture->width as the radius.
$ship_texture = LoadTexture("Ships.png");
$ship = create_ship($ship_texture);

function reset_game(&$ship, &$num_of_asteroids) {
    global $asteroids, $width, $height;

    $ship->game_lost = false;
    $ship->pos = new Vector2($width/2, $height/2);

    $asteroids = array();
    create_asteroids($asteroids, $num_of_asteroids, $ship);
    $ship->score = 0;
    $ship->life = 2;
}

$lost_texture = LoadTexture("Lost.png");
$win_loss_texture = LoadTexture("Win_Loss.png");

$game_paused = false;

// Asteroids creation
$num_of_asteroids = 4;
create_asteroids($asteroids, $num_of_asteroids, $ship);
while (!WindowShouldClose()) {
    if ($game_paused) {
        if (IsKeyPressed(KEY_SPACE)) {
            $game_paused = false;
            $ship->game_lost = false;
            reset_game($ship, $num_of_asteroids);
            $ship = create_ship($ship_texture);
        }
    } else {
        // Ship movement
        if (IsKeyDown(KEY_W) || IsKeyDown(KEY_UP)) {
            $ship->acceleration += 800.0;
        } else {
            $ship->acceleration -= 7.0;
        }
        if (IsKeyDown(KEY_A) || IsKeyDown(KEY_LEFT)) {
            // TODO: handle the speed better.
            $ship->angle -= $angle_speed * GetFrameTime();
            if ($ship->angle <= 0) $ship->angle = 360.0;
        }
        if (IsKeyDown(KEY_D) || IsKeyDown(KEY_RIGHT)) {
            $ship->angle += $angle_speed * GetFrameTime();
            if ($ship->angle >= 360) $ship->angle = 0.0;
        }

        move_ship($ship);
        if ($ship->velocity < 0) {
            $ship->velocity = 0;
            $ship->acceleration = 0;
        }

        if (IsKeyPressed(KEY_SPACE)) {
            array_push($ship->bullets, new Bullet(
                new Vector2($ship->pos->x, $ship->pos->y),
                $ship->angle,
                4));
        }
        ship_collision($asteroids, $ship);
        ship_out_of_bounds($ship);

        // Asteroids
        move_asteroids($asteroids);
        draw_asteroids($asteroids);

        // Bullets
        $num_of_bullets = count($ship->bullets);
        $bullets_text = "$num_of_bullets";
        $bullets_text_size = MeasureText($bullets_text, 24);
        DrawText($bullets_text, $width/2-$bullets_text_size, $height-40, 24, Color::GREEN());

        move_bullets($ship->bullets, $ship->angle);
        draw_bullets($ship->bullets);
        destroy_bullets($ship->bullets);
        destroy_asteroids($ship->bullets, $asteroids, $ship->score);

        // Win and and Loss conditions
        if ($ship->game_lost) {
            $game_paused = true;
        }
        elseif (count($asteroids) <= 0) {
            $game_paused = true;
        }
    }

    BeginDrawing();
    ClearBackground(Color::Black());

    DrawFPS(0, 0);

    // Score and Life
    $score_text = "score: $ship->score";
    DrawText($score_text, $width-MeasureText($score_text, 24)-8, 0, 24, Color::GREEN());

    $life_text = "life: $ship->life";
    DrawText($life_text, $width-MeasureText($life_text, 24)-8, 24, 24, Color::GREEN());

    // Ship 
    //if ($ship->life < 2) {
    //    UnloadTexture($texture);
    //    $texture = LoadTexture("Broken_Ship.png");
    //}
    DrawTexturePro(
        $ship->texture, 
        new Rectangle(20*$ship->life, 0, (int)$ship->texture->width/2, (int)$ship->texture->height), 
        new Rectangle($ship->pos->x, $ship->pos->y, $ship->texture->width/2, $ship->texture->height), 
        new Vector2($ship->radius/2, $ship->radius/2),
        $ship->angle+90, 
        Color::WHITE());

    // Debug 
    //DrawCircleV(
    //    new Vector2(
    //        $ship->pos->x + 20 * cos(d2r($ship->angle)),
    //        $ship->pos->y + 20 * sin(d2r($ship->angle))),
    //    4,
    //    Color::YELLOW()
    //);

    //DrawCircleV($ship->pos, 4, Color::BLUE());

    // Experimental
    $v = "velocity: $ship->velocity";
    $a = "acceleration: $ship->acceleration";
    DrawText($v, 0, $height-48, 24, Color::GREEN());
    DrawText($a, 0, $height-24, 24, Color::GREEN());
    // Experimental

    if ($game_paused) {
        $scale = 1.5;
        $x = (int)($width/2)  - (int)($win_loss_texture->width/4*$scale);
        $y = (int)($height/2) - (int)($win_loss_texture->height/2*$scale);

        // Loss 
        if ($ship->game_lost) { 

            //DrawTextureEx(
            //    $lost_texture, 
            //    new Vector2($x, $y),
            //    0, 
            //    $scale, 
            //    Color::WHITE());

            DrawTexturePro(
                $win_loss_texture, 
                new Rectangle(
                    0, 0, $win_loss_texture->width/2, $win_loss_texture->height
                ), 
                new Rectangle(
                    $x, $y, $win_loss_texture->width/2*$scale, $win_loss_texture->height*$scale 
                ), 
                new Vector2(0, 0), 
                0, 
                Color::WHITE());

            $loss_text = "Voce Perdeu!";
            DrawText($loss_text, (int)($width/2)-(int)(MeasureText($loss_text, 24)/2), $win_loss_texture->height*$scale-48, 24, Color::RED());
        }
        
        else { // Win 
            //DrawTextureEx(
            //    $win_texture, 
            //    new Vector2($x, $y),
            //    0, 
            //    $scale, 
            //    Color::WHITE());
            DrawTexturePro(
                $win_loss_texture, 
                new Rectangle(
                    $win_loss_texture->width/2, 0, $win_loss_texture->width/2, $win_loss_texture->height
                ), 
                new Rectangle(
                    $x, $y, $win_loss_texture->width/2*$scale, $win_loss_texture->height*$scale 
                ), 
                new Vector2(0, 0), 
                0, 
                Color::WHITE());

            $win_text = "Parabens, Voce ganhou!";
            DrawText($win_text, (int)($width/2)-(int)(MeasureText($win_text, 24)/2), $win_loss_texture->height*$scale-48, 24, Color::GREEN());
        }

        $continue_text = "Aperte [SPACE] para continuar!";
        DrawText($continue_text, (int)($width/2)-(int)(MeasureText($continue_text, 24)/2), $win_loss_texture->height*$scale-10, 24, Color::GREEN());
    }

    EndDrawing();
}

UnloadTexture($ship_texture);
UnloadTexture($win_loss_texture);
CloseWindow();

?>
