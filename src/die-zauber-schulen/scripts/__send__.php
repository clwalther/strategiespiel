<?php

include "../../scripts/global.php";
include "./_general_.php";
include "./_utils_.php";

$general = new General();
$utils = new Utils();

include "./buildings.php";
include "./students.php";
include "./teachers.php";
include "./labour.php";
include "./prestige.php";

$buildings = new Buildings();
$students  = new Students();
$teachers  = new Teachers();
$labour    = new Labour();
$prestige  = new Prestige();

foreach($_POST as $key => $values) {
    // converts previously converted key back
    $key = urldecode($key);

    foreach(explode(",", $values) as $value) {
        // converts previously converted value back
        $value = urldecode($value);

        // LEXER
        switch($key)
        {
            // general
            // backup
            case "general_backup":
                $general->backup("RQSTD");
                break;

            case "general_load_backup":
                $general->load_backup(BACKUP_FILE_PATH.$value);
                break;

            // launch/halt/reset/backup
            case "general_reset":
                $general->reset();
                break;

            case "general_start":
                $general->start();
                break;

            case "general_pause":
                $general->pause();
                break;

            // team
            case "general_change_name":
                $general->change_name($value);
                break;


            // ministry of labour
            // prestige / influence
            case "prestige_add":
                $prestige->add_value($value);
                break;

            case "influence_add":
                $labour->add_influence($value);
                break;
            // labour
            case "labour_set_base":
                $labour->set_base($value);
                break;

            case "labour_set_advanced":
                $labour->set_advanced($value);
                break;

            case "labour_add_worker":
                $labour->add_worker($value); // adds a new worker of given type
                break;

            case "labour_add_base":
                $labour->add_base($value); // set the base value for the last worker of given type
                break;

            case "labour_add_advanced":
                $labour->add_advanced($value); // set the advanced value for the last worker of given type
                break;

            case "labour_delete_worker":
                $labour->delete_worker($value);
                break;

            case "labour_change_influence":
                $labour->change_influence($value);
                break;

            // ministry of school administration
            // buildings
            case "building_activate":
                $buildings->activate($value);
                break;

            case "building_deactivate":
                $buildings->deactivate($value);
                break;

            // students
            case "students_check_out":
                $students->check_out($value);
                break;

            // teachers
            case "teachers_set_base":
                $teachers->set_base($value);
                break;

            case "teachers_set_advanced":
                $teachers->set_advanced($value);
                break;

            case "teacher_displacement_set":
                $teachers->set_displacement($value);
                break;
        }
    }
}

$database->close();

?>