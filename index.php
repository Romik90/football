<?
declare(strict_types=1);
require_once 'data.php';

if (!function_exists('match')) {
	function match(int $c1, int $c2) : array {
		global $data;
		if (empty($data))
			die("Нет данных для анализа");
		$first_team_data = $data[$c1];
		$second_team_data = $data[$c2];
		if (empty($first_team_data) || empty($second_team_data))
			return [];

		$total_scored = 0;
		$total_skipped = 0;
		foreach ($data as $team) {
			$total_scored += $team['goals']['scored'] / $team['games'];
			$total_skipped += $team['goals']['skiped'] / $team['games'];
		}
		$avg_scored = $total_scored / count($data);
		$avg_skipped = $total_skipped / count($data);

		$avg_scored_1 = $first_team_data['goals']['scored'] / $first_team_data['games'];
		$avg_scored_2 = $second_team_data['goals']['scored'] / $second_team_data['games'];

		$avg_skipped_1 = $first_team_data['goals']['skiped'] / $first_team_data['games'];
		$avg_skipped_2 = $second_team_data['goals']['skiped'] / $second_team_data['games'];

		$attack_power_1 = $avg_scored_1 / $avg_scored;
		$attack_power_2 = $avg_scored_2 / $avg_scored;

		$defense_power_1 = $avg_skipped_1 / $avg_skipped;
		$defense_power_2 = $avg_skipped_2 / $avg_skipped;

		$expected_goals_first = $attack_power_1 * $defense_power_2 * $avg_scored_1;
		$expected_goals_second = $attack_power_2 * $defense_power_1 * $avg_scored_2;

		foreach ([$expected_goals_first, $expected_goals_second] as $goals) {

			$goal_distribution = [];
			$min = $max = 0;
			for ($j=0; $j < 10; $j++) { 
				$result = 1;
				for($i = 1; $i <= $j; $i++) {
				    $result *= $i;
				}
				$equity = pow($goals, $j) * exp(-$goals) / $result;
				$max = $equity * 100 + $min;

				$goal_distribution[$j] = [
					'min' => $min,
					'max' => $max,
				];
				$min = $max;
			}

			$rand = mt_rand(0, 99);
			foreach ($goal_distribution as $amount => $ev) {
				if ($rand >= $ev['min'] && $rand < $ev['max']) {
					$score[] = $amount;
					break;
				}
			}
		}

		return $score;
	}
}

$uri = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim($uri, '/'));

if (count($segments) == 2) {
	$team_1 = (int)$segments[0];
	$team_2 = (int)$segments[1];
} else {
	$team_1 = 0;
	$team_2 = 1;
}

if ($team_2 == $team_1)
	die("Выбраны одинаковые команды");

$res = match($team_1, $team_2);
echo "<h1 align='center'>Счет</h1><br>";
echo "<h2 align='center'>{$data[$team_1]['name']} {$res[0]} : {$res[1]} {$data[$team_2]['name']}</h2>";