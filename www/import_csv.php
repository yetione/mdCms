<?php
$csvFile = fopen('_data/base.csv', 'r');
$dsn = "mysql:host=46.252.168.212;dbname=qspace;port=3306;charset=utf8";
//$dsn = 'mysql:dbname=qspace;host=46.252.168.212';
$user = 'qspace';
$password = 'qspass';

$db = new PDO($dsn, $user, $password);

$addInstitute = $db->prepare('INSERT INTO `institute`(`name`,`full_name`) VALUES (?, ?)');
$addGroup = $db->prepare('INSERT INTO `group`(`name`) VALUES (?)');

$addStudent = $db->prepare('INSERT INTO `student` (`institute_id`, `group_id`, `course`, `name`, `med_group`, `department`, `specialization_id`, `teacher_id`, `time_1`, `time_2`, `student_id`, `user_id`)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');

$addCertification = $db->prepare('INSERT INTO `certification`(`student_id`, `course`, `october`, `theory_1`, `practice_1`, `march`, `theory_2`, `practice_2`, `norm`) VALUES (?, ?, 0,0,0,0,0,0,0)');
$institutes = array(
    'ИЭИ'=>4,
);
$groups = array();

$fName='fName';
$stdCountS = 0;
$stdCount = 0;
$medGroups = array('основная', 'специальная', 'подготовительная');
$departments = array('спортивное', 'основное', 'специальное');
while(($row=fgetcsv($csvFile,0,';')) !== false){

    $name = trim($row[0]);
    $student_id = trim($row[1]);
    $group = trim($row[2]);
    $instituteName = trim($row[3]);
    $instituteName = trim($instituteName, $instituteName[0].$instituteName[1]);
    //var_dump(trim(preg_replace('/\s+/', ' ', $instituteName)));
    if (!isset($groups[$group])){
        $addGroup->bindParam(1, $group);
        $addGroup->execute();
        $groups[$group] = $db->lastInsertId();
    }
    $groupId = $groups[$group];
    $teacherId = 1;
    if (!isset($institutes[$instituteName])){
        $addInstitute->bindParam(1, $instituteName);
        $addInstitute->bindParam(2, $fName);
        $addInstitute->execute();
        $institutes[$instituteName] = $db->lastInsertId();
    }
    $instituteId = $institutes[$instituteName];
    $course = $group[0];
    $medGroup = $medGroups[rand(0,2)];
    $department = $departments[rand(0,2)];
    $specializationId = rand(1, 48);
    $teacherId = rand(4,90);
    $time1 = 'Time1';
    $time2 = 'Time2';
    $studentId = $student_id;
    $usrId = null;
    $addStudent->bindParam(1, $instituteId);
    $addStudent->bindParam(2, $groupId);
    $addStudent->bindParam(3, $course);
    $addStudent->bindParam(4, $name);
    $addStudent->bindParam(5, $medGroup);
    $addStudent->bindParam(6, $department);
    $addStudent->bindParam(7, $specializationId);
    $addStudent->bindParam(8, $teacherId);
    $addStudent->bindParam(9, $time1);
    $addStudent->bindParam(10, $time2);
    $addStudent->bindParam(11, $studentId);
    $addStudent->bindParam(12, $usrId);
    $stdCount++;
    if ($addStudent->execute()){
        $stdCountS++;
    }else{
        var_dump($addStudent->errorInfo());
    }

    $addCertification->bindParam(1, $db->lastInsertId());
    $addCertification->bindParam(2, $course);
    $addCertification->execute();
}
var_dump('Всего студентов: '.$stdCount);
var_dump('Добавлено: '.$stdCountS);