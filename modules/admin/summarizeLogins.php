<?php


    ## edw ftia3e tis hmeromhnies
    $current_month = date('Y-m-01 00:00:00');

    $sql_0 = "SELECT min(`when`) as min_date, max(`when`) as max_date FROM loginout";   //gia na doume
        
    $result = db_query($sql_0, $mysqlMainDb);
    while ($row = mysql_fetch_assoc($result)) {
        $min_date = $row['min_date'];
        $max_date = $row['max_date'];
    }
    mysql_free_result($result);

    

    $minstmp = strtotime($min_date);
    $maxstmp = strtotime($max_date);
    

    if ( $minstmp + 62*24*3600 < $maxstmp ) { #data more than two months old

        $end_stmp = strtotime($min_date)+ 31*24*60*60;  //min time + 1 month
        $start_date = $min_date;
        $end_date = date('Y-m-01 00:00:00', $end_stmp);
        

        while ($end_date < $current_month){

                $sql_1 = "SELECT count(idLog) as visits FROM loginout ".
                    " WHERE `when` >= '$start_date' AND `when` < '$end_date' AND action='LOGIN'";

                $result_1 = db_query($sql_1, $mysqlMainDb);
                while ($row1 = mysql_fetch_assoc($result_1)) {
                    $visits = $row1['visits'];
                }
                mysql_free_result($result_1);
                
                $sql_2 = "INSERT INTO loginout_summary SET ".
                    " login_sum = '$visits', ".
                    " start_date = '$start_date', ".
                    " end_date = '$end_date' ";
                $result_2 = db_query($sql_2, $mysqlMainDb);
                @mysql_free_result($result_2);

                $sql_3 = "DELETE FROM loginout ".
                    "WHERE `when` >= '$start_date' AND ".     #PROSOXH sbhnoume kai ta LOGOUT
                    " `when` < '$end_date' ";
                $result_3 = db_query($sql_3, $mysqlMainDb);
                @mysql_free_result($result_3);

            

            #next month
            $start_date = $end_date;
            $end_stmp =strtotime($end_date)+ 31*24*60*60;  //end time + 1 month
            $end_date = date('Y-m-01 00:00:00', $end_stmp);
           
        }
    }
?>
