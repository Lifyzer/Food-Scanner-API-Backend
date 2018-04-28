<?php
include_once "ConstantValues.php";

function addData(PDO $connection,$function_name="",$table_name,$dataArray)
{
    $status=FAILED;
    $message=NO_ERROR;
    $sql="";
    $data=array();
    try {
        $numItems = count($dataArray);
        $cnt = 0;
        $fields = "";
        $values = "";
        foreach ($dataArray as $key => $value) {
            if (++$cnt == $numItems) {
                $fields .= $key;
                $values .= ":" . $key;
            }
            else {
                $fields .= $key . ",";
                $values .= ":" . $key . " , ";
            }
        }
        $sql = "INSERT INTO " . $table_name . " (" . $fields . " ) VALUES (" . $values . ") ";
        if($stmt = $connection->prepare($sql)){
            foreach ($dataArray as $key => $value){
                $stmt->bindValue(":$key",$value,PDO::PARAM_STR);
            }
            if($stmt->execute()){
                $status=SUCCESS;
                $message=$connection->lastInsertId();
                $stmt->closeCursor();
            }
        }
    }
    catch (PDOException $exception) {
        $message=$exception->getMessage();
        $err_msg = "\nFunction=> ".$function_name . " Query=> " . $sql . "  Error message= " . $message;
        errorLogFunction($err_msg);
        if(is_array($message)){
            $message = implode(" , ", $message);
        }
    }
    $data['status']=$status;
    $data['message']=$message;
    $data['sql']=$sql;
    return $data;
}

function editData(PDO $connection,$function_name="",$table_name,$dataArray,$conditionArray,$query="")
{
    $sql="";
    $data = array();
    try {
        $numOfItems = count($dataArray);
        $numOfItemsForCondition = count($conditionArray);
        $cnt = 0;
        $cntForCondition = 0;
        $values = "";
        $conditionValue = "";
        $execute_arr = array();

        foreach ($dataArray as $key => $value) {
            if(empty($query)){
                if (++$cnt == $numOfItems) {
                    $values .= $key . " = :$key ";
                }
                else {
                    $values .= $key . " = :$key , ";
                }
            }
            $execute_arr[":$key"] = $value;
        }
        foreach ($conditionArray as $key => $value) {
            if (++$cntForCondition == $numOfItemsForCondition) {
                $conditionValue .= $key . " = :$key ";
            }
            else {
                $conditionValue .= $key . " = :$key AND ";
            }
            $execute_arr[":$key"] = $value;
        }
        if(empty($query)){
            $sql = "UPDATE " . $table_name . " SET " . $values . " WHERE " . $conditionValue;
        }
        else{
            $sql=$query;
        }
        $stmt = $connection->prepare($sql);
          foreach ($dataArray as $key => $value){
              $stmt->bindValue(":$key",$value);
          }
          foreach ($conditionArray as $key => $value){
              $stmt->bindValue(":$key",$value);
          }
        $stmt->execute();
        $status = SUCCESS;
        $message = UPDATE_SUCCESS;
        $stmt->closeCursor();
    } catch (PDOException $exception) {
        $status = FAILED;
        $message = $exception->getMessage();
        $err_msg = "\nFunction=> " . $function_name . " Query=> " . $sql . "  Error message= " . $message;
        errorLogFunction($err_msg);
    }
    $data['status'] = $status;
    $data['message'] = $message;
    $data['sql'] = $sql;
    return $data;
}

function checkDataExist(PDO $connection,$table,$columns,$sql,$dataArray)
{
    try {
        $numOfItems = count($dataArray);
        $execute_array = array();
        $cnt = 0;
        $condition="";
        foreach ($dataArray as $key => $value){
            if(empty($sql)){
                if (++$cnt == $numOfItems) {
                    $condition .= $key . " = :$key ";
                }
                else {
                    $condition .= $key . " = :$key AND ";
                }
            }
            $execute_array[":$key"] =$value;
        }
        if(empty($sql)){
           $sql= "SELECT ".$columns." FROM " . $table . " WHERE " . $condition;
        }
        $statement = $connection->prepare($sql);
        foreach ($dataArray as $key => $value){
            $statement->bindValue(":$key",$value);
        }
        $statement->execute();
        return $statement->rowCount();
    } catch (Exception $e) {
        $message=$e->getMessage();
        if(is_array($message)){
            $error_message = implode(" , ", $message);
        }
        else{
            $error_message=$message;
        }
        return $error_message;
    }
}

function getSingleTableData(PDO $connection,$table,$sql,$columns,$customCondition,$dataArray)
{
    try {
        $numOfItems = count($dataArray);
        $cnt = 0;
        $execute_array = array();
        $condition = "";

        if(!empty($dataArray)){
            foreach ($dataArray as $key => $value) {
                if (empty($sql)) {
                    if (empty($customCondition)) {
                        if (++$cnt == $numOfItems) {
                            $condition .= $key . " = :$key ";
                        }
                        else {
                            $condition .= $key . " = :$key AND ";
                        }
                    }
                }
                $execute_array[":$key"] = $value;
            }
        }

        if (empty($customCondition)) {
            $check_conditions = $condition;
        }
        else {
            $check_conditions = $customCondition;
        }
        if (empty($sql)) {
            $sql = "SELECT " . $columns . " FROM " . $table . " WHERE " . $check_conditions;
        }
        $statement = $connection->prepare($sql);

        if(!empty($dataArray)){
            foreach ($dataArray as $key => $value) {
                $statement->bindValue(":$key", $value);
            }
        }
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
    } catch (PDOException $e) {
        $message = $e->getMessage();
        $result['message'] = $message;
        $err_msg = "\nFunction=> " . " Query=> " . $sql . "  Error message= " . $message;
        errorLogFunction($err_msg);
    }
    return $result;
}

function getSingleTableDataLastDate(PDO $connection,$table,$sql,$columns,$customCondition,$dataArray)
{
    try {
        $numOfItems = count($dataArray);
        $cnt = 0;
        $execute_array = array();
        $condition = "";
        $statement = "";
        if(!empty($dataArray)){
            foreach ($dataArray as $key => $value) {
                if (empty($sql)) {
                    if (empty($customCondition)) {
                        if (++$cnt == $numOfItems) {
                            $condition .= $key . " = :$key ";
                        }
                        else {
                            $condition .= $key . " = :$key AND ";
                        }
                    }
                }
                $execute_array[":$key"] = $value;
            }
        }

        if (empty($customCondition)) {
            $check_conditions = $condition;
        }
        else {
            $check_conditions = $customCondition;
        }
        if (empty($sql)) {
            $sql = "SELECT " . $columns . " FROM " . $table . " WHERE " . $check_conditions;
        }
        $sql = $sql." ORDER BY modified_date DESC";

        $statement = $connection->prepare($sql);
        if(!empty($dataArray)){
            foreach ($dataArray as $key => $value) {
                $statement->bindValue(":$key", $value);
            }
        }
        $statement->execute();
        $result = array();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
    } catch (PDOException $e) {
        $message = $e->getMessage();
        $result['message'] = $message;
        $err_msg = "\nFunction=> " . " Query=> " . $sql . "  Error message= " . $message;
        errorLogFunction($err_msg);
    }
    return $result;
}

function getMultipleTableData(PDO $connection,$table,$sql,$columns,$customCondition, array $dataArray = null)
{
    try{
        $numOfItems = count($dataArray);
        $cnt = 0;
        $execute_array=array();
        $condition="";
        $check_conditions="";
        if(!empty($dataArray)){
            foreach ($dataArray as $key => $value){
                if(empty($sql)){
                    if(empty($customCondition)){
                        if (++$cnt == $numOfItems) {
                            $condition .= $key . " = :$key ";
                        }
                        else {
                            $condition .= $key . " = :$key AND ";
                        }
                    }
                }
                $execute_array[":$key"] =$value;
            }
        }

        if(empty($customCondition)){
            $check_conditions=$condition;
        }
        else{
            $check_conditions=$customCondition;
        }
        if(empty($sql)) {
            if (empty($sql)) {
                $sql = "SELECT " . $columns . " FROM " . $table . " WHERE " . $check_conditions;
            }
        }
        $statement = $connection->prepare($sql);
        if(!empty($dataArray)){
            foreach ($dataArray as $key => $value) {
                $statement->bindValue(":$key", $value);
            }
        }

        $statement->execute();
    }
    catch (PDOException $e){
        $err_msg = "\nFunction=> " . " Query=> " . $sql . "  Error message= " . $e->getMessage();
        errorLogFunction($err_msg);
        $message=$e->getMessage();
        if(is_array($message)){
            $error_message = implode(" , ", $message);
        }
        else{
            $error_message=$message;
        }
        return $error_message;
    }
    return $statement;
}

function editDataWithCustomCondition(PDO $connection,$function_name="",$table_name,$dataArray,$conditionArray,$setValues)
{
    $status=FAILED;
    $message=NO_ERROR;
    $sql="";
    $data=array();
    try {
        $numOfItemsForCondition = count($conditionArray);
        $cntForCondition = 0;
        $conditionValue="";
        $execute_arr=array();

        foreach ($dataArray as $key => $value) {
            $execute_arr[":$key"]= $value;
        }
        foreach ($conditionArray as $key => $value){
            if (++$cntForCondition == $numOfItemsForCondition) {
                $conditionValue .=  $key . " = :$key ";
            }
            else {
                $conditionValue .= $key . " = :$key AND ";
            }
            $execute_arr[":$key"]= $value;
        }
        $sql = "UPDATE " . $table_name . " SET ".$setValues." WHERE ".$conditionValue;
        $statement = $connection->prepare($sql);
        foreach ($dataArray as $key => $value) {
            $statement->bindValue(":$key", $value);
        }
        foreach ($conditionArray as $key => $value) {
            $statement->bindValue(":$key", $value);
        }
        $result=$statement->execute();
        if($result){
            $status=SUCCESS;
            $message=UPDATE_SUCCESS;
        }
        else{
            $status=FAILED;
            $message=$statement->errorInfo();
        }
    } catch (PDOException $exception) {
        $message=$exception->getMessage();
        $err_msg = "\nFunction=> ".$function_name . " Query=> " . $sql . "  Error message= " . $message;
        errorLogFunction($err_msg);
    }
    $data['status']=$status;
    $data['message']=$message;
    $data['sql']=$sql;
    return $data;
}
