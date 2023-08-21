<?php

class GraduatesGenerator {
    function __construct() {
        global $database;
        global $utils;

        $this->database = $database;
        $this->utils = $utils;

        //
        $file = file_get_contents(DATA_FILE_PATH);
        $file = json_decode($file, true);

        $this->buildingsJson = $file["buildings"];
        $this->generalJson = $file["general"];

        $this->tribePerkSumArray = array();
        foreach ($this->generalJson['subjects'] as $subject) {
            $this->tribePerkSumArray[$subject] = 0;
        }

        $this->addBuildingPerksWithArray($this->generalJson['initial_skillperks'], $this->tribePerkSumArray);
    }

    private function calculateNewGraduate($groupId) {
        $perkSumArray = $this->createPerkSumArray();
        $this->iterateBuildings($this->buildingsJson, $groupId, $perkSumArray);
        $this->iterateTeachers($groupId, $perkSumArray);
        $this->addDisplacements($groupId, $perkSumArray);
        return $this->createGraduate($perkSumArray);
    }

    private function addDisplacements($groupId, &$perkSumArray) {
        foreach ($perkSumArray as $key => $value) {
            $perkSumArray[$key] += $this->get_displacement($groupId, $key);
        }
    }

    private function createGraduate($perkSumArray) {

        $randomNumber = mt_rand(1, 100);

        switch (true) {
            case ($randomNumber <= 10):
                $this->prepareOneSubjectSpecialist($perkSumArray);
                break;

            case ($randomNumber <= 20):
                $this->prepareTwoSubjectSpecialist($perkSumArray);
                break;

            default:
                $this->prepareGeneralist($perkSumArray);
                break;
        }

        $graduate = [];

        foreach ($perkSumArray as $subject => $perkSum) {
            $p = $perkSum / 7; // Wahrscheinlichkeit basierend auf perkSum
            $graduate[$subject] = $this->generateBinomialRandom(7, $p);
        }

        return $graduate;
    }

    private function generateBinomialRandom($n, $p) { //TODO: Stellschraube
        $x = 0;
        for ($i = 0; $i < $n; $i++) {
            if (mt_rand() / mt_getrandmax() <= $p) {
                $x++;
            }
        }
        return $x;
    }

    private function prepareOneSubjectSpecialist(&$perkSumArray) {
        $randomSubject = array_rand($perkSumArray);
        foreach ($perkSumArray as $subject => &$value) {
            if ($subject === $randomSubject) {
                $value += 3;
            } else {
                $value -= 1;
            }
        }
    }

    private function prepareTwoSubjectSpecialist(&$perkSumArray) {
        $randomSubjects = array_rand($perkSumArray, 2);
        foreach ($perkSumArray as $subject => &$value) {
            if (in_array($subject, $randomSubjects)) {
                $value += 2;
            } else {
                $value -= 1.2;
            }
        }
    }

    private function prepareGeneralist(&$perkSumArray) {
        //Nothing to do here
    }

    private function createPerkSumArray() {
        return array_merge([], $this->tribePerkSumArray);
    }

    private function iterateBuildings($buildingRoot, $groupId, &$perkSumArray) {
        foreach ($buildingRoot as $key => $building) {
            if($this->utils->get_building_status($this->utils->get_building_id($key), $groupId)){
                $this->addBuildingPerksWithArray($building["perks"], $perkSumArray);
            }
            if ($building["children"] != "none") {
                $this->iterateBuildings($building["children"], $groupId, $perkSumArray);
            }
        }
    }

    private function iterateTeachers($groupId, &$perkSumArray) {
        $teachers = $this->get_teachers($groupId);
        foreach ($teachers as $teacher) {
            $this->addTeacherPerk($teacher["name"], ($teacher["base"]+$teacher["advanced"]), $perkSumArray);
        }
    }

    private function addBuildingPerksWithArray($perkArray, &$perkSumArray) {
        foreach ($perkArray as $key => $value) {
            if(in_array($key, $this->generalJson["subjects"])) {
                $perkSumArray[$key] += $value*3; //TODO Stellschraube
            }
        }
    }

    private function addTeacherPerk($name, $skill, $perkSumArray) {
        $perkSumArray[$name] += $skill/2.5; //TODO Stellschraube
    }

    // === ** ===
    private function get_teachers(int $group_id) {
        // aquires the file contents
        $file = file_get_contents(DATA_FILE_PATH);
        $file = json_decode($file, true);
        // return array carrying teachers
        $send_teachers = array();

        // aquires correct group frim group id with all teachers
        $group = $this->database->select_where("SCHOOL_ADMIN", $file["general"]["subjects"], ["group_id" => $group_id]);

        foreach($group[0] as $teacher => $skill_repre) {
            // converts skill represetnation to definite int value
            $skill_repre = floatval($skill_repre);
            // gets the ammount skills that exist for extraction
            $n_skills = count($file["general"]["subjects"]);

            // aquires the currently required skill index from the teachers subject/profession
            $skill_index = array_search($teacher, $file["general"]["subjects"]);

            // extracts the values from base and advanced representation
            $base_value = $this->utils->get_base($skill_repre, $skill_index);
            $advanced_value = $this->utils->get_advanced($skill_repre, $skill_index);

            // fills the teacher structure
            $teacher_struct = ["name" => $teacher, "base" => $base_value, "advanced" => $advanced_value];

            // pushes into carrying object
            array_push($send_teachers, $teacher_struct);
        }

        // returns the carrier
        return $send_teachers;
    }

    private function get_displacement(int $group_id, string $subject): float {
        // aquires the file contents
        $file = file_get_contents(DATA_FILE_PATH);
        $file = json_decode($file, true);

        $table = sprintf("%s_displacement", $subject);

        $group = $this->database->select_where("SCHOOL_ADMIN", [$table], ["group_id" => $group_id]);

        return $group[0][$table];
    }

    public function generate_graduate(int $group_id): void {
        // fetches and inserts a graduate into STUDENTS database for given group
        // aquires the file contents
        $file = file_get_contents(DATA_FILE_PATH);
        $file = json_decode($file, true);

        // generating the student
        $student = $this->calculateNewGraduate($group_id);

        // converting
        $generate_value = 0;

        foreach($student as $skill_name => $skill_value) {
            // aquire skill index for skill name
            $skill_index = array_search($skill_name, $file["general"]["subjects"]);

            // compresses the skill value into student representation
            $generate_value = $this->utils->add_base($generate_value, $skill_index, $skill_value);
        }

        // makes sure that graduate never has no skills
        if($generate_value > 0) {
            // getting student ready for insertion
            $new_student = [
                "group_id" => $group_id,
                "value" => $generate_value
            ];

            // inserting student
            $this->database->insert("STUDENTS", $new_student);
        } else {
            // recalls if no skills
            $this->generate_graduate($group_id);
        }
    }
}

?>
