<?php

session_start();


$link = mysqli_connect('glitter', 'pmauser', 'ok', 'twitter');

if (mysqli_connect_errno()) {

    print_r(mysqli_connect_error());
    exit();

}

if ($_GET['function'] == "logout") {

    session_unset();

}

function time_since($time)
{

    $time = time() - $time; // to get the time since that moment
    $time = ($time<1)? 1 : $time;
    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return ' '. $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
    }

}

function displayTweets($type) {
    global $link;
    $whereClause = "";
    if ($type == 'public') {
     
        $whereClause = "";

    } else if ($type == 'isFollowing') {

        $query = "SELECT * FROM isFollowing WHERE follower = ". mysqli_real_escape_string($link, $_SESSION['id']);
        $result = mysqli_query($link, $query);
        while ($row = mysqli_fetch_assoc($result)) {

            if ($whereClause == "") $whereClause = "WHERE";
            else $whereClause .= " OR";

            $whereClause .= " userid = ".$row['isFollowing'];

        }

    } else if ($type == 'yourtweets') {

        $whereClause = "WHERE userid = ". mysqli_real_escape_string($link, $_SESSION['id']);

    } else if ($type == 'search') {

        echo "<p>Showing result(s) for '". mysqli_real_escape_string($link, $_GET['q']). "'</p>";
        $whereClause = "WHERE tweet LIKE '%". mysqli_real_escape_string($link, $_GET['q']). "%'";

    } else if (is_numeric($type)) {

        $userQuery = "SELECT * FROM `users` WHERE id = ". mysqli_real_escape_string($link, $type). " LIMIT 1";

        $userQueryResult = mysqli_query($link, $userQuery);

        $user = mysqli_fetch_assoc($userQueryResult);

        echo "<h2>". mysqli_real_escape_string($link, $user['email']). "'s tweets</h2>";

        $whereClause = "WHERE userid = ". mysqli_real_escape_string($link, $type);
    }

    $query = "SELECT * FROM `tweets` ". $whereClause. " ORDER BY `datetime` DESC LIMIT 10";

    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) == 0) {
        echo "There are no tweets to display";
    } else {
        
        while ($row = mysqli_fetch_assoc($result)) {
            
            $userQuery = "SELECT * FROM `users` WHERE id = ". mysqli_real_escape_string($link, $row['userid']). " LIMIT 1";
            
            $userQueryResult = mysqli_query($link, $userQuery);

            $user = mysqli_fetch_assoc($userQueryResult);

            echo "<div class='tweet'>";

            echo "<p><a href='?page=publicprofiles&userid=". $user['id']. "'>". $user['email']. "</a> <span class='time'>". time_since(strtotime($row['datetime'])). "</span> ago</p>";

            echo "<p>". $row['tweet']. "</p>";
            
            $reln = "";

            $isFollowingQuery = "SELECT * FROM isFollowing WHERE follower = ". mysqli_real_escape_string($link, $_SESSION['id']). " AND isFollowing = ". mysqli_real_escape_string($link, $row['userid']). " LIMIT 1";
            
            $isFollowingResult = mysqli_query($link, $isFollowingQuery);

            if (mysqli_num_rows($isFollowingResult) > 0) {

                $reln = "Unfollow";

            }else {

                $reln = "Follow";

            }

            echo "<p><a style='text-decoration: underline' class='toggleFollow' data-userId='". $row['userid']. "'>". $reln. "</a></p>";

            echo "</div>";
        }
    }
}

function displaySearch() {
    
    echo '<form>
  <div class="form-row align-items-center">
    <div class="col-auto">
      <input type="hidden" name="page" value=search>
      <input type="text" name="q" class="form-control mb-2" id="search" placeholder="Search">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary mb-2">Search Tweets</button>
    </div>
  </div>
</form>';

}

function displayTweetBox() {

    if ($_SESSION['id'] > 0) {
        
        echo '
        <div style="display: none;" id="tweetSuccess" class="alert alert-success">Your tweet was posted successfully</div>
        <div style="display: none;" id="tweetFail" class="alert alert-danger"></div>
        <div class="form">
  <div class="form-row align-items-center">
    <div class="col">
        <textarea class="form-control mb-2" id="tweetContent"></textarea>
    </div>
  </div>
    <button id="postTweetButton" class="btn btn-primary mb-2">Post Tweets</button>
</div>';

    }

}

function displayUsers () {

    global $link;

    $query = "SELECT * FROM users LIMIT 10";

    $result = mysqli_query($link, $query);

    while ($row = mysqli_fetch_assoc($result)) {

        echo "<p><a href='?page=publicprofiles&userid=". $row['id']. "'>". $row['email']. "</p>";

    }

}

?>
