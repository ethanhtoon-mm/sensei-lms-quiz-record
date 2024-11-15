<?php

function display_quizr_dash(){
    $search_query = isset($_POST['user_search']) ? sanitize_text_field($_POST['user_search']) : '';
   ?>

    <div class="wrap">
    <h1>Quiz Attempt Records</h1>

        <form method="post" style="margin-top: 15px">
            <input type="text" name="user_search" placeholder="Search by Name or Email" value="<?php echo esc_attr($search_query); ?>">
            <input type="submit" class="button button-secondary" value="Search">
        </form>

        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Course Title</th>
                <th>No of Attempt</th>
                <th>Score</th>
                <th>Incorrect Questions</th>
            </tr>
            </thead>
        <tbody>
        
        <?php
        $user_args = [
            'fields' => ['ID','user_login','user_email']
        ];
        if(!empty($search_query)){
            $user_args['search'] = '*' . esc_attr($search_query) . '*';
            $user_args['search_columns'] = ['user_login','user_email'];
        }
 
        $users = get_users($user_args);
        foreach ($users as $user) {
            $attempt_records = get_field('attempt_records', 'user_' . $user->ID);

            if ($attempt_records) :

                // Sorting
                usort($attempt_records, function ($a, $b) {
                    if ($a['course_title'] == $b['course_title']) {
                        $attempt_a = isset($a['attempt_number']['num']) ? $a['attempt_number']['num'] : 0;  // Default to 0 if 'num' doesn't exist
                        $attempt_b = isset($b['attempt_number']['num']) ? $b['attempt_number']['num'] : 0;  // Default to 0 if 'num' doesn't exist
                        return $attempt_a <=> $attempt_b;
                    }
                    return strcmp($a['course_title'], $b['course_title']);
                });
                
                foreach ($attempt_records as $record) :
            ?>
                <tr>
                    <td><?php echo esc_html($user->user_login); ?></td>
                    <td><?php echo esc_html($user->user_email); ?></td>
                    <td><?php echo esc_html($record['course_title']); ?></td>
                    
                    <td>
                        <?php
                        if (is_array($record['attempt_number']) && isset($record['attempt_number']['num'])) {
                            echo '<b>'.esc_html($record['attempt_number']['num']) . '</b>';
                        } 
                        ?> 
                        
                        <i>
                        <?php
                        if (is_array($record['attempt_number']) && isset($record['attempt_number']['datetime'])) {
                            echo ' - Date: ' . esc_html($record['attempt_number']['datetime']) . '';
                        } 
                        ?>
                        </i>
                    </td>
                    <td class="<?= ($record['score'] < 100) ? 'failed' : 'success' ?>"><?php echo esc_html($record['score']); ?>%</td>
                    <td class="quiz_lists">
                        <?php
                        if (!empty($record['incorrect_questions'])) { ?>
                            <ul>
                            <?php
                            foreach ($record['incorrect_questions'] as $incorrect) : ?>
                                <li><?= esc_html($incorrect['ques']) ?><?php if($incorrect['ans']): ?><i style=" color: #2323ff; "><br><strong>Ans: </strong><?= is_array($incorrect['ans']) ? $incorrect['ans'][0] : $incorrect['ans'] ?></i><?php endif; ?></li>
                            <?php endforeach;
                            ?>
                            </ul>
                            <?php
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </td>
                </tr>
            <?php
                endforeach;
            else:
                ?>
                <tr>
                <td><?php echo esc_html($user->user_login); ?></td>
                    <td><?php echo esc_html($user->user_email); ?></td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>
                        -
                    </td>
                </tr>
                <?php
            endif;
        }
        ?>
        </tbody>
        </table>
    </div>
    <form method="POST" style="margin-top: 20px">
        <input type="submit" name="export_csv" value="Export CSV" class="button button-primary" id="">
    </form>
<?php
}
