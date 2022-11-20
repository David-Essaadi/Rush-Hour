<?php
    ini_set("memory_limit", 1024*1024*1024);
    enum Direction
    {
        case Right;
        case Down;
        case Left;
        case Up;
    }

    solve(file_get_contents("input.txt"));

    function solve(string $input): void
    {
        $start = hrtime(true);
        $initial_state = preg_split("/\r\n|\n|\r/", $input);
        $queue = new SplQueue();
        $queue->enqueue([$initial_state, []]);
        $board_size = strlen($initial_state[0]); // Get board size before our search.
        $visited = array();
        $i = 0;

        // Perform breadth-first search
        while (!$queue->isEmpty()) {
            $i++;
            [$state, $moves] = $queue->dequeue();
            // BFS, so we add the neighbours to the queue.
            // Neighbours are all possible moves in this configuration.

            if (!isset($visited[implode($state)])) {
                queue_moves($state, $queue, $moves, $board_size);
                $visited[implode($state)] = true;
            }

            // Check for a win
            if ($state[2][4] == "r" && $state[2][5] == "r") {
                $end = hrtime(true);
                echo "Final configuration:\n";
                print_r($state);
                echo "Finished in " . $i . " iterations.\n";
                echo "Solving took " . ($end - $start) / 1000000 . "ms.\n";
                echo "Solved in " . sizeof($moves) . " moves. \n";
                print_moves($moves);
                break;
            }
        }
    }

    // Prints the given moves ($vehicle, Direction) in sequence.
    function print_moves($moves): void {
        foreach ($moves as [$vehicle, $move]) {
            echo $vehicle . "-" . $move->name . "\n";
        }
    }

    // Does the dirty work. Finds all possible moves in the given $state and adds the resulting states to the queue.
    // Assumed is that every line is of equal length.
    // Assumed is that every vehicle is at least of size 2.
    // Assumed is that every vehicle has a unique, single-character identifier.
    // Assumed is that every vehicle is horizontally or vertically connected.
    function queue_moves(array $state, SplQueue $queue, array $moves, int $board_size): void
    {
        $processed_vehicles = ""; // Every letter in this string corresponds to a vehicle we have already processed.

        // Find vehicles and see if they can move
        for ($y = 0; $y < $board_size; $y++) {
            for ($x = 0; $x < $board_size; $x++) {
                $row = $state[$y];
                $char = $row[$x];
                if (!$char != "." && !str_contains($processed_vehicles, $char)) {
                    // By our assumptions we know for sure that there is either a horizontal or a vertical vehicle.
                    // If the size is greater than 1, we have a horizontal vehicle.
                    if(($size = horizontal_check($x, $board_size, $row, $char)) > 1) {
                        $left = $x;
                        $right = $x + $size - 1;

                        // Left
                        if ($x > 0 && $row[$x - 1] == ".") {
                            $new_state = move_horizontal($state, $y, $char, $right, $left - 1);
                            add_to_queue($queue, $new_state, $moves, $char, Direction::Left);
                        }

                        // Right
                        if ($right + 1 < $board_size && $row[$right + 1] == ".") {
                            $new_state = move_horizontal($state, $y, $char, $left, $right + 1);
                            add_to_queue($queue, $new_state, $moves, $char, Direction::Right);
                        }
                    }
                    else if(($size = vertical_check($x, $y, $board_size, $state, $char)) > 1) {
                        $top = $y;
                        $bottom = $y + $size - 1;

                        // Up
                        if ($y > 0 && $state[$y - 1][$x] == ".") {
                            $new_state = move_vertical($state, $x, $char, $bottom, $top - 1);
                            add_to_queue($queue, $new_state, $moves, $char, Direction::Up);
                        }

                        // Down
                        if ($bottom + 1 < $board_size && $state[$bottom + 1][$x] == ".") {
                            $new_state = move_vertical($state, $x, $char, $top, $bottom + 1);
                            add_to_queue($queue, $new_state, $moves, $char, Direction::Down);
                        }
                    }
                    $processed_vehicles .= $char;
                }
            }
        }
    }

    // Returns a new state in which a given horizontal move is performed.
    function move_horizontal($state, $y, $char, $from, $to): array
    {
        $new_state = $state;
        $new_state[$y][$to] = $char;
        $new_state[$y][$from] = ".";
        return $new_state;
    }

    // Returns a new state in which a given vertical move is performed.
    function move_vertical($state, $x, $char, $from, $to): array
    {
        $new_state = $state;
        $new_state[$to][$x] = $char;
        $new_state[$from][$x] = ".";
        return $new_state;
    }

    // The queue consists of entries in the form ($char, $direction). Example: ("A", Direction::Left)
    function add_to_queue(SplQueue $queue, array $new_state, array $moves, string $char, Direction $direction) : void
    {
        $queue->enqueue([$new_state, array_merge($moves, [array($char, $direction)])]);
    }

    //Horizontally check for a vehicle (we look from left to right, so we only need to check right).
    function horizontal_check($x, $board_size, $row, $char) : int
    {
        $size = 1;
        for ($z = $x; $z < $board_size - 1; $z++) {
            if ($row[$z + 1] == $char) {
                $size++;

            }
        }
        return $size;
    }

    //Vertically check for a vehicle (we look from top to bottom, so we only need to check down).
    function vertical_check($x, $y, $board_size, $array, $char) : int
    {
        $size = 1;
        for ($z = $y + 1; $z < $board_size; $z++) {
            if ($array[$z][$x] == $char) {
                $size++;
            }
        }
        return $size;
    }
