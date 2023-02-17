<?php
/**
 * BwireTech
 *
 * This class helps to fetch results of various national examinations in Tanzania.
 *
 * @class           NectaAPI
 * @developer       Bwire Mashauri
 * @phone           +255 689 938 643
 * @email           mashauri@programmer.net
 */
class NectaAPI
{
    public function getResults($data)
    {
        $index_no = $data["index_no"];
        $exam_year = $data["exam_year"];
        $exam_type = $data["exam_type"];

        $level = $exam_type;
        $year = $exam_year;
        $center_id = strtolower(strstr($index_no, "/", true));
        $remove_key = strstr($index_no, "/", true);

        $valid_y = $this->validate_year($year, $level);

        if ($valid_y == "1") {
            $data_link = $this->get_link($year, $level, $center_id);
            $exam_results = $this->scrap_results(
                $data_link,
                $index_no,
                $remove_key
            );
            return $exam_results;
        } else {
            $return["message"] = "Examination year is currently not supported";
            return $return;
        }
    }

    public function getSchool($data)
    {
        $school_no = $data["school_no"];
        $exam_year = $data["exam_year"];
        $exam_type = $data["exam_type"];

        $level = $exam_type;
        $year = $exam_year;
        $center_id = $school_no;
        $remove_key = "";

        $valid_y = $this->validate_year($year, $level);

        if ($valid_y == "1") {
            $data_link = $this->get_link($year, $level, $center_id);
            $school_results = $this->scrap_school(
                $data_link,
                $school_no,
                $remove_key
            );
            return $school_results;
        } else {
            $return["message"] = "Examination year is currently not supported";
            return $return;
        }
    }

    public function comparison($data)
    {
        $start_year = $data["start_year"];
        $end_year = $data["end_year"];
        $level = $data["exam_type"];
        $schools_dt = explode(",", $data["schools"]);
        $remove_key = "";

        $valid_y1 = $this->validate_year($start_year, $level);
        $valid_y2 = $this->validate_year($end_year, $level);

        if ($valid_y1 == 1 and $valid_y2 == 1) {
            $schools = [];
            while ($start_year <= $end_year) {
                foreach ($schools_dt as $school) {
                    $data_link = $this->get_link($start_year, $level, $school);
                    $remove_key = "" . strtoupper($school) . " ";
                    $comparison_results = $this->scrap_comparison(
                        $data_link,
                        $school,
                        $remove_key
                    );
                    $sing_school = [
                        "exam_year" => $start_year,
                        "school_name" => $comparison_results["school_name"],
                        "school_gpa" =>
                            $comparison_results["examination_center_gpa"],
                        "students" =>
                            $comparison_results["registered_students"],
                        "position_nationwide" =>
                            $comparison_results["center_position_nationwide"],
                    ];
                    array_push($schools, $sing_school);
                }
                $start_year++;
            }
            return $schools;
        } else {
            $return["message"] = "Examination year is currently not supported";
            return $return;
        }
    }

    public function schoolList($data)
    {
        $exam_year = $data["exam_year"];
        $exam_type = $data["exam_type"];

        $level = $exam_type;
        $year = $exam_year;
        $remove_key = "";

        $valid_y = $this->validate_year($year, $level);

        if ($valid_y == "1") {
            $data_link = $this->get_school_link($year, $level);
            $school_results = $this->scrap_school_list($data_link);
            return $school_results;
        } else {
            $return["message"] = "Examination year is currently not supported";
            return $return;
        }
    }

    public function validate_year($year, $level)
    {
        if (!defined("NECTA_URL")) {
            define("NECTA_URL", "https://onlinesys.necta.go.tz/results/");
        }
        if (!defined("NECTA_URL_ALT")) {
            define("NECTA_URL_ALT", "https://matokeo.necta.go.tz/");
        }
        if (!defined("LEVELS")) {
            define("LEVELS", "CSSE,ACSSE");
        }
        if (!defined("O_LIMITS")) {
            define("O_LIMITS", "2015,2016,2017,2018,2019,2020,2021,2022");
        }
        if (!defined("A_LIMITS")) {
            define("A_LIMITS", "2014,2015,2016,2017,2018,2019,2020,2021,2022");
        }
        if (!defined("O_LIMITS_ALT")) {
            define("O_LIMITS_ALT", "");
        }
        if (!defined("O_LIMITS_ALT_V2")) {
            define("O_LIMITS_ALT_V2", "2022");
        }
        if (!defined("A_LIMITS_ALT")) {
            define("A_LIMITS_ALT", "");
        }
        if (!defined("A_LIMITS_ALT_V2")) {
            define("A_LIMITS_ALT_V2", "");
        }

        $GLOBALS["level_list"] = explode(",", LEVELS);
        $GLOBALS["o_limits"] = explode(",", O_LIMITS);
        $GLOBALS["o_limits_alt"] = explode(",", O_LIMITS_ALT);
        $GLOBALS["o_limits_alt_v2"] = explode(",", O_LIMITS_ALT_V2);
        $GLOBALS["a_limits"] = explode(",", A_LIMITS);
        $GLOBALS["a_limits_alt"] = explode(",", A_LIMITS_ALT);
        $GLOBALS["a_limits_alt_v2"] = explode(",", A_LIMITS_ALT_V2);

        switch ($level) {
            case "CSEE":
                if (in_array($year, $GLOBALS["o_limits"])) {
                    return 1;
                } else {
                    return 0;
                }
                break;

            case "ACSEE":
                if (in_array($year, $GLOBALS["a_limits"])) {
                    return 1;
                } else {
                    return 0;
                }
                break;

            default:
                return 0;
        }
    }

    public function get_link($year, $level, $center_id)
    {
        switch ($level) {
            case "CSEE":
                if (in_array($year, $GLOBALS["o_limits_alt"])) {
                    $data_link =
                        NECTA_URL_ALT .
                        "results" .
                        $year .
                        "/csee/results/" .
                        $center_id .
                        ".htm";
                    return $data_link;
                } elseif (in_array($year, $GLOBALS["o_limits_alt_v2"])) {
                    $data_link =
                        NECTA_URL_ALT .
                        "csee" .
                        $year .
                        "/results/" .
                        $center_id .
                        ".htm";
                    return $data_link;
                } else {
                    $data_link =
                        NECTA_URL .
                        "" .
                        $year .
                        "/" .
                        strtolower($level) .
                        "/results/" .
                        $center_id .
                        ".htm";
                    return $data_link;
                }
                break;

            case "ACSEE":
                if (in_array($year, $GLOBALS["a_limits_alt"])) {
                    $data_link =
                        NECTA_URL_ALT .
                        "acsee2022/results/" .
                        $center_id .
                        ".htm";
                    return $data_link;
                } else {
                    $data_link =
                        NECTA_URL .
                        "" .
                        $year .
                        "/" .
                        strtolower($level) .
                        "/results/" .
                        $center_id .
                        ".htm";
                    return $data_link;
                }
        }
    }

    public function get_school_link($year, $level)
    {
        switch ($level) {
            case "CSEE":
                if (in_array($year, $GLOBALS["o_limits_alt"])) {
                    $data_link =
                        NECTA_URL_ALT . "csee" . $year . "/csee/csee.htm";
                    return $data_link;
                } else {
                    $data_link = NECTA_URL . "" . $year . "/csee/csee.htm";
                    return $data_link;
                }
                break;

            case "ACSEE":
                if (in_array($year, $GLOBALS["a_limits_alt"])) {
                    $data_link = NECTA_URL_ALT . "acsee" . $year . "/index.htm";
                    return $data_link;
                } else {
                    $data_link = NECTA_URL . "" . $year . "/acsee/index.htm";
                    return $data_link;
                }
        }
    }

    public function scrap_results($data_link, $index_no, $remove_key)
    {
        if (($data = @file_get_contents($data_link)) === false) {
            $error = error_get_last();
            $return["message"] = "HTTP request failed : " . $error["message"];
            return $return;
        } else {
            $homepage = file_get_contents($data_link);
            $stringArr = explode("\n", strip_tags($homepage));
            $result_found = 0;
            foreach ($stringArr as $key => $value) {
                if (strstr($value, $index_no)) {
                    $result_found = 1;
                    $return["message"] = "Results fetched successfully";
                    $return["school_name"] = str_replace(
                        $remove_key,
                        "",
                        $stringArr[7]
                    );
                    $return["candidate_gender"] = $stringArr[$key + 2];
                    $return["division"] = $stringArr[$key + 6];
                    $return["aggregated_marks"] = $stringArr[$key + 4];
                    $return["detailed_subjects"] = str_replace(
                        "'",
                        "",
                        explode("-", $stringArr[$key + 8])
                    );
                }
            }

            if ($result_found == "0") {
                $return["message"] = "Invalid parameters were passed";
            }

            return $return;
        }
    }

    public function scrap_school($data_link, $index_no, $remove_key)
    {
        if (($data = @file_get_contents($data_link)) === false) {
            $error = error_get_last();
            $return["message"] = "HTTP request failed : " . $error["message"];
        } else {
            $homepage = file_get_contents($data_link);
            $stringArr = explode("\n", $homepage);
            $return["message"] = "Results fetched successfully";
            $return["division_1"] = strip_tags($stringArr[59]);
            $return["division_2"] = strip_tags($stringArr[61]);
            $return["division_3"] = strip_tags($stringArr[63]);
            $return["division_4"] = strip_tags($stringArr[64]);
            $return["division_0"] = strip_tags($stringArr[67]);
            $return["female_students"] =
                strip_tags($stringArr[31]) +
                strip_tags($stringArr[33]) +
                strip_tags($stringArr[35]) +
                strip_tags($stringArr[37]) +
                strip_tags($stringArr[39]);
            $return["male_students"] =
                strip_tags($stringArr[45]) +
                strip_tags($stringArr[47]) +
                strip_tags($stringArr[49]) +
                strip_tags($stringArr[51]) +
                strip_tags($stringArr[53]);

            foreach ($stringArr as $key => $value) {
                if (strstr($value, "EXAMINATION CENTRE RANKING")) {
                    $return["examination_center_region"] = strip_tags(
                        $stringArr[$key + 4]
                    );
                    $return["total_passed_candidates"] = strip_tags(
                        $stringArr[$key + 6]
                    );
                    $return["examination_center_gpa"] = strip_tags(
                        $stringArr[$key + 8]
                    );
                    $return["center_category"] = strip_tags(
                        $stringArr[$key + 10]
                    );
                    $return["center_position_regionwide"] = strip_tags(
                        $stringArr[$key + 12]
                    );
                    $return["center_position_nationwide"] = strip_tags(
                        $stringArr[$key + 14]
                    );
                    $return["registered_students"] = strip_tags(
                        $stringArr[$key + 34]
                    );
                    $return["absent_students"] = strip_tags(
                        $stringArr[$key + 35]
                    );
                    $return["withheld"] = strip_tags($stringArr[$key + 37]);
                }
            }
        }
        return $return;
    }

    public function scrap_school_list($data_link)
    {
        function startsWith($string, $startString)
        {
            $len = strlen($startString);
            return substr($string, 0, $len) === $startString;
        }
        $return = [];

        if (($data = @file_get_contents($data_link)) === false) {
            $error = error_get_last();
            $return["message"] = "HTTP request failed : " . $error["message"];
        } else {
            $homepage = file_get_contents($data_link);
            $stringArr = explode("\n", strip_tags($homepage));
            foreach ($stringArr as $key => $value) {
                if ($key >= 100) {
                    if ($value != "") {
                        if (!startsWith($value, "P")) {
                            if (startsWith($value, "S")) {
                                $reg_no = substr($value, 0, 5);
                                $school_name = str_replace(
                                    "$reg_no ",
                                    "",
                                    $value
                                );
                                $school_info = [
                                    "school_no" => $reg_no,
                                    "school_name" => rtrim($school_name),
                                ];
                                array_push($return, $school_info);
                            }
                        }
                    }
                }
            }
        }
        return $return;
    }

    public function scrap_comparison($data_link, $index_no, $remove_key)
    {
        if (($data = @file_get_contents($data_link)) === false) {
            $error = error_get_last();
            $return["message"] = "HTTP request failed : " . $error["message"];
        } else {
            $homepage = file_get_contents($data_link);
            $stringArr = explode("\n", $homepage);
            $return["message"] = "Results fetched successfully";

            foreach ($stringArr as $key => $value) {
                if (strstr($value, "EXAMINATION CENTRE RANKING")) {
                    $return["school_name"] = str_replace(
                        $remove_key,
                        "",
                        strip_tags($stringArr[7])
                    );
                    $return["examination_center_gpa"] = strip_tags(
                        $stringArr[$key + 8]
                    );
                    $return["center_position_nationwide"] = strip_tags(
                        $stringArr[$key + 14]
                    );
                    $return["registered_students"] = strip_tags(
                        $stringArr[$key + 34]
                    );
                }
            }
        }

        return $return;
    }
}

?>
