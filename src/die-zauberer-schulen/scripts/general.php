<?php

define('BACKUP_FILE_PATH', "/var/www/html/The-Wizard-Schools/conf.d/backups/die-zauberer-schulen/", true);


class General
{
    function __construct() {
        global $database;

        $this->database = $database;
    }

    // UTILS
    public function backup(string $name = "STNDRT"): void {
        // list of all the tables
        $table_names = ["TIME", "TEAM", "SCHOOL_ADMIN", "STUDENTS", "LABOUR", "WORKERS"];

        // path to backup folder with timestamp
        $time_struct = date("Y-m-d H:i:s", time());
        $folder_path = sprintf(BACKUP_FILE_PATH."%s %s", $time_struct, $name);

        // creates the backup folder with the timestamp
        mkdir($folder_path);

        // loops through all the tables
        foreach ($table_names as $table_name) {
            // path to file
            $file_path = sprintf("%s/%s.csv", $folder_path, $table_name);
            // backups the table
            $this->database->backup_table($table_name, $file_path);
        }
    }

    public function load_backup(string $folder_path): void {
        // creates a safety backup
        $this->backup("BFR-LDNG");

        // list of all the tables
        $table_names = ["TIME", "TEAM", "SCHOOL_ADMIN", "STUDENTS", "LABOUR", "WORKERS"];

        // loops through all the tables
        foreach ($table_names as $table_name) {
            // creates the file pointer
            $file_path = sprintf("%s/%s.csv", $folder_path, $table_name);
            // loads the table
            $this->database->load_table_backup($table_name, $file_path);
        }
    }

    public function get_backups(): array {
        $folder_path = "/var/www/html/The-Wizard-Schools/conf.d/backups/die-zauberer-schulen/";

        // returning the all folder enties in backup folder without the two standart dot-folders
        return array_values(array_diff(scandir($folder_path), array('..', '.')));
    }

    public function reset(): void { // [TODO]
        // creates a safety backup
        $this->backup("BFR-RST");
    }

    // TIME SPECIFIC
    public function start(): void {
        // creates a safety backup
        $this->backup("BFR_STRT");
        // writes a start time log into time-database
        $this->database->insert("TIME", ["time" => time(), "type" => true]);
    }

    public function pause(): void {
        // creates a safety backup
        $this->backup("BFR-PAUS");
        // writes a halt/stop time log into time-database
        $this->database->insert("TIME", ["time" => time(), "type" => false]);
    }

    public function get_times(): array {
        // reqads all time logs from database
        $time_logs = $this->database->select("TIME", ["time", "type"]);

        // 1.: gets the last type from database to determine wheter halted or not
        // 2.: writes all of the time logs into time logs send object
        $send_times = [
            "is_running" => end($time_logs)["type"],
            "times" => $time_logs
        ];

        // returns the time array object
        return $send_times;
    }

    // TEAM SPECIFIC
    public function get_teams(): array {
        // reads and returns the team array object
        return $this->database->select("TEAM", ["*"]);
    }

    public function change_name($value): void {
        // changes the name on a team
        $bundle = explode(";", $value);

        $id   = $bundle[0];
        $name = $bundle[1];

        $this->database->update("TEAM", ["teamname" => $name], ["group_id" => $id]);
    }

    // SKILLS [TODO]
    public function get_skills(): array {
        return [
            ["name" => "Zaubertränke", "base" => 0, "advanced" => 0],
            ["name" => "Zauberkunst",  "base" => 0, "advanced" => 0],
            ["name" => "Verteidigung", "base" => 0, "advanced" => 0],
            ["name" => "Geschichte",   "base" => 0, "advanced" => 0],
            ["name" => "Geschöpfe",    "base" => 0, "advanced" => 0],
            ["name" => "Kräuterkunde", "base" => 0, "advanced" => 0],
            ["name" => "Besenfliegen", "base" => 0, "advanced" => 0]
        ];
    }
}

?>
