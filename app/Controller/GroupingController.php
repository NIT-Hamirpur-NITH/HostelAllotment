<?php

App::uses('Sanitize', 'Utility');
class GroupingController extends AppController
{
   var $name =  'Panel';
     function beforeFilter() {
        parent::beforeFilter();
      
        if(!$this->isLogin) 
        {
            $this->redirect(BASE_URL . '/user/login');  
        }
        else if($this->admin)
        {
            $this->redirect(BASE_URL . '/user/logout');
        }
        else
        {
            if($this->groupingactive==0)
            {
                 $this->redirect(BASE_URL . '/user/logout');
            }
            else
            {$name = $this->Session->read('user');
          $name = $name['username'];
    
         
         $result = $this->Panel->reloadlist($name);
         
         $this->set('result',$result);
            }
        }
    }
    
     function idcard()
    {
        $name = $this->Session->read('user');
        $rollno = $this->request->query['rollno'];
        $userinfo = $this->Panel->otheruserinfo($rollno,$name);
        $imagelink = $this->Panel->query("select imageadd from imageupload where rollno='$rollno' ");
        if(!empty($imagelink))
        $imagelink = $imagelink[0]['imageupload']['imageadd'];
        else
        $imagelink='thumbnail.jpg';
        $name=strtoupper($userinfo['name']);
        $mobileno = $userinfo['mobileno'];
        $email = $userinfo['email'];
        $cgpi= $userinfo['cgpi'];
        $dept = $userinfo['department'];
        $dob = $userinfo['dob'];
        echo "<div class='mainidcard'> 
            
        <div class='top'>
            <div class='topleft' >
            <span class='idtext' style='font-weight;font-weight: bold;font-size: 20px;'> $name </span> <br />
      <span class='testing' > Roll no : </span>  <span class='idtext'> $rollno </span> <br />
      <span class='testing'  > CGPI : </span>      <span class='idtext'> $cgpi </span> <br />
      <span class='testing'  > Dept : </span>      <span class='idtext'> $dept </span> <br />
       <span class='testing'  > DOB : </span>      <span class='idtext'> $dob </span> <br /> 
            
            </div>
            
            <div class='topright'>
            <img height='135' width='120' src='/hostel/faltoo/".$imagelink."'>
            </div>
        <div>
        
        <div class='bottom'>
         <div class='bottomleft'> <span class='idtext'> $email </span> <br /> </div>
         <div class='bottomright'>   <span class='idtext'> $mobileno </span>    <br /></div>
        </div>

        </div>";
        exit;
    }
    
    
    
     public function newacceptreject()
   {
       $getdata=$this->request->query['value'];
       $name = $this->Session->read('user');
          $rollno = $name['username'];
if($getdata=="reject")
{
     $sender=$this->request->query['sender'];
    $query="delete from `request` where `sender`=$sender and `receiver` in (select `groupid` from `group` where `rollno`='$rollno')";
    $this->Panel->query($query);
    
}
if($getdata=="accept")
{
     $sender=$this->request->query['sender'];
     $query="select `receiver` from `request` where `sender`=$sender and `receiver` in (select `groupid` from `group` where `rollno`='$rollno')";
     $result=$this->Panel->query($query);
     if(!empty($result))
     {
        $query="delete from `request` where `sender`=$sender or `receiver`=$sender or exists(select * from `group` where `rollno`='$rollno' and (`groupid`=`receiver` or `groupid`=`sender`))";       
        $this->Panel->query($query);
        $receiver=$result[0]['request']['receiver'];
        $query="update `group` set `groupid`='$sender' where `groupid`='$receiver'"; 
        $this->Panel->query($query);   
     }
}
if($getdata=="leavegroup")
{
    
    $query="delete from `request` where exists (select * from `group` where `rollno`='$rollno' and (`groupid`=`receiver` or `groupid`=`sender`))";
    $this->Panel->query($query);
    $query="delete from `group` where `rollno`='$rollno'";
   $this->Panel->query($query);
   $query="insert into `group`(`rollno`) values ('$rollno')";
   $this->Panel->query($query);
}
if($getdata=="deleterequest")
{
    $receiver=$this->request->query['receiver'];
    $query="delete from `request` where `receiver`=$receiver and `sender` in (select `groupid` from `group` where `rollno`='$rollno')";
    $this->Panel->query($query);
}

$this->redirect("$this->baseurl/grouping/requestnew");
//window.location = "http://localhost/hostel/index.php/grouping/requestnew"; 
   }
    
     public function sendrequest()
   {
         
         
      $getdata=  $this->request->query['choice'];
     $rollnor=$getdata;
$query="select `groupid` from `group` where `rollno`='$rollnor'";
$result=$this->Panel->query($query);
$idr=$result[0]['group']['groupid'];
$cansend=true;
$name = $this->Session->read('user');
          $rollno = $name['username'];
          
 $query="select `year`,`gender`,`course` from `users` where `rollno`='$rollnor'";
$result=$this->Panel->query($query);
 if($result[0]['users']['year']==$name['year'] && $result[0]['users']['course']==$name['course'] && $result[0]['users']['gender']==$name['gender'])
  {
$query="select `groupid` from `group` where `rollno`='$rollno'";
$result=$this->Panel->query($query);
$ids=$result[0]['group']['groupid'];
$query = "select `rollno`,g.`groupid`,u.`name` from `group` as g Natural Join `users` as u where g.`groupid` = $ids order by `ncgpi` desc";
$result=$this->Panel->query($query);
$i=0;
foreach($result as $data)
{
    if($data['g']['rollno']!=$rollno && $i==0)
    $cansend=false;
    $i++;
}
$c1=$i;
$query = "select `rollno`,g.`groupid`,u.`name` from `group` as g Natural Join `users` as u where g.`groupid` = $idr order by `ncgpi` desc";
$result=$this->Panel->query($query);
$i=0;
foreach($result as $data)
{
    if($data['g']['rollno']!=$getdata && $i==0)
    $cansend=false;
    $i++;
}
if($c1+$i>$this->groupsize)
    $cansend=false;
$query="insert into `request` values ('$ids','$idr')";
if($cansend)
$this->Panel->query($query);

  }
  $this->redirect("$this->baseurl/grouping/requestnew");
/* <script type="text/javascript">
window.location = "http://localhost/hostel/index.php/grouping/requestnew"; 
</script>
   */    
   }
   
   
    public function requestnew()
    {
        $cansend=false;
        $user=  $this->Session->read('user');
        $rollno=$user['rollno'];
         $query = "select `rollno`,g.`groupid`,u.`name`,u.`year`,u.`gender`,u.`course` from `group` as g Natural Join `users` as u where g.`groupid` in (select `groupid` from `group` where `rollno` = '$rollno') order by `ncgpi` desc";
                $usergroup=$this->Panel->query($query);
                 $grouplen=count($usergroup);      
                $this->set('usergroup',$usergroup);
              if($usergroup[0]['g']['rollno']==$rollno)
                  $cansend=true;
        $queryforrank="select temp.groupid,ncgpi,temp.capacity from (select groupid,max(ncgpi) as ncgpi,max(cgpi) as cgpi ,count(*) as capacity from users natural join `group` where year='$user[year]' and course='$user[course]' and gender='$user[gender]' 
                                                                     group by groupid order by groupid,ncgpi desc) as temp order by temp.ncgpi desc,temp.cgpi desc,temp.groupid";
        $result=$this->Panel->query($queryforrank);
        $grouprank=array();
        $i=0;
        foreach($result as $data)
        {
            $i++;
            $value = $data['temp']['groupid'];
            $grouprank[]=array('groupid'=>$data['temp']['groupid'],"rank"=>$i,$data['temp']['ncgpi'],'capacity' => $data['temp']['capacity']);
            $rankgroup[$data['temp']['groupid']]=array('rank' => $i);
        }
        
        $query="select g.`groupid`,`rollno` from `group` as g NATURAL JOIN `users` as u where `year`='$user[year]' and `course`='$user[course]' and `gender`='$user[gender]' and g.`groupid` not in (select `groupid` from `group` where rollno='$user[rollno]') and g.groupid not in 
        (select * from (select `sender` as `groupid` from `request` where `receiver`=".$usergroup[0]['g']['groupid']." union select `receiver` as `groupid` from `request` where `sender`=".$usergroup[0]['g']['groupid'].") as abc order by `groupid`) order by g.`groupid`, u.`ncgpi` desc";
        $result=$this->Panel->query($query);
        $group1 = array();
        $temp = array();
        $prev = 0;
         $query="select g.`groupid`,`rollno` from `group` as g NATURAL JOIN `users` as u where `year`='$user[year]' and `course`='$user[course]' and `gender`='$user[gender]' and g.groupid in (select * from (select `sender` as `groupid` from `request` where `receiver`=".$usergroup[0]['g']['groupid']." union select `receiver` as `groupid` from `request` where `sender`=".$usergroup[0]['g']['groupid']." union select `groupid` from `group` where rollno='$rollno') as abc order by `groupid`) order by g.`groupid`, u.`ncgpi` desc";                      
           $result1=  $this->Panel->query($query);
                        $maxlen= $this->groupsize;
                        $data=0;
                         foreach($result as $data)
                        {
                            if($prev != $data['g']['groupid'] && $prev != 0)
                            {  
                                if(count($temp[$prev]) + $grouplen <= $maxlen )
                                { 
                                    $group1[]= $temp;
                                }
                                else
                                     { $i=0;
                                         foreach($temp[$prev] as $navneet)
                                         {   
                                             $temp[$prev][$i][1]=0;
                                             $i++;
                                         }
                                         $group1[] = $temp;
                                         
                                     }
                                     
                                //$temp = array();                          
                            }
                            $temp[$data['g']['groupid']][]= array($data['g']['rollno'],1);
                            $prev=$data['g']['groupid'];
                            $roll=$data['g']['rollno'];
                        }
                        if(count($temp[$prev]) + $grouplen > $maxlen )
                        {
                                         $i=0;
                                         foreach($temp[$prev] as $navneet)
                                         {   
                                             $temp[$prev][$i][1]=0;
                                             $i++;
                                         }
                        }
                         $group1[] = $temp;
                        $prev=0;
                        
                        //$temp=array();
                        foreach($result1 as $data)
                        {
                            if($prev != $data['g']['groupid'] && $prev != 0)
                            {  
                                    $group1[] = $temp;
                               // $temp = array();                          
                            }
                            $temp[$data['g']['groupid']][] =array($data['g']['rollno'],0);
                                  
                            $prev=$data['g']['groupid'];
                            $roll=$data['g']['rollno'];
                        }
                         $group1[] = $temp;
                    //pr($temp);
                        $this->set('ranklist',$grouprank);
                        $this->set('grouprollno',$temp);
                        $this->set('rankgroup',$rankgroup);
                    $rollnog=$usergroup[0]['g']['rollno'];
                    if($rollno==$rollnog)
                    {
                        $query="select sender from request where receiver in (select `groupid` from `group` where `rollno`='$rollno')";
                         $result=  $this->Panel->query($query);
                         $groupidslist=array();
                    foreach($result as $data)
                    {
                        $groupidslist[]=array($data['request']['sender']);
   /*                                       
     echo ' <form method="get" action="http://localhost/hostel/index.php/grouping/newacceptreject" >';
     echo "<input type='hidden' name='sender' value='$groupids' />";
     echo "<input type='submit' name='value' value='accept' />";
     echo "<input type='submit' name='value' value='reject' /></form>";
     */
                    } 
   $this->set('sidlist',$groupidslist);
    $query="select receiver from request where sender in (select `groupid` from `group` where `rollno`='$rollno')";
    $result=  $this->Panel->query($query);
                         $groupidrlist=array();
                    foreach($result as $data)
                    {
                        $groupidrlist[]=array($data['request']['receiver']);
   /*                                       
     echo ' <form method="get" action="http://localhost/hostel/index.php/grouping/newacceptreject" >';
     echo "<input type='hidden' name='sender' value='$groupids' />";
     echo "<input type='submit' name='value' value='accept' />";
     echo "<input type='submit' name='value' value='reject' /></form>";
     */
                    }
   $this->set('ridlist',$groupidrlist);
}
 $this->set('cansend',$cansend);
    }}
?>
