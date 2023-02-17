## Necta-API
A PHP Class used to fetch results of various national examinations in Tanzania

## Installation
```php
//Include the Necta-API Class in your project
require_once ('necta_api.php');
//Create a new instance of the Necta-API Class
$necta = new NectaAPI();
```

## Usage
**1. Fetch results for a single student.**
```php
$query = array("index_no"=>"STUDENT NUMBER","exam_year"=>"EXAM YEAR","exam_type"=>"EXAM TYPE");
$results = $necta->getResults($query);
print_r($results);
```

**2. Fetch school overall results.**
```php
$query = array("school_no"=>"SCHOOL NUMBER","exam_year"=>"EXAM YEAR","exam_type"=>"EXAM TYPE");
$results = $necta->getSchool($query);
print_r($results);
```

**3. Compare schools performance.**
```php
$query = array("start_year"=>"START YEAR","end_year"=>"END YEAR","exam_type"=>"EXAM TYPE","schools"=>"school number 1, school number 2, school number 3....");
$results = $necta->comparison($query);
print_r($results);
```

**4. Fetch school list on a specific year.**
```php
$query = array("exam_year"=>"EXAM YEAR","exam_type"=>"EXAM TYPE");
$results = $necta->schoolList($query);
print_r($results);
```

**5. Examination type and years supported:**
```
CSEE - 2015 to 2022
ACSEE - 2014 to 2022
```
