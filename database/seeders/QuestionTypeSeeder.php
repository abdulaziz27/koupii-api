<?php

namespace Database\Seeders;

use App\Models\QuestionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QuestionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $questionTypes = [
            [
                'name' => 'Multiple Choice (Single Answer)',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'multiple_choice',
                    'select_mode' => 'single',
                    'options' => 'variable',
                ]),
                'scoring_rules' => json_encode(['correct' => 1, 'incorrect' => 0]),
            ],
            [
                'name' => 'Multiple Choice (Multiple Answers)',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'multiple_choice',
                    'select_mode' => 'multiple',
                    'options' => 'variable',
                ]),
                'scoring_rules' => json_encode(['correct_per_item' => 1, 'partial_credit' => true]),
            ],
            [
                'name' => 'True/False/Not Given',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'true_false_not_given',
                    'select_mode' => 'single',
                    'options_fixed' => true,
                    'options' => ['True', 'False', 'Not Given'],
                ]),
                'scoring_rules' => json_encode(['correct' => 1, 'incorrect' => 0]),
            ],
            [
                'name' => 'Matching Headings',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'matching',
                    'left_side' => 'headings',
                    'right_side' => 'paragraphs',
                    'select_mode' => 'multiple',
                ]),
                'scoring_rules' => json_encode(['correct' => 1, 'incorrect' => 0]),
            ],
            [
                'name' => 'Sentence Completion',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'completion',
                    'format' => 'sentence',
                    'blanks' => 'in_sentence',
                    'word_limit' => '1-3 words',
                ]),
                'scoring_rules' => json_encode(['correct' => 1, 'case_sensitive' => false]),
            ],
            [
                'name' => 'Paragraph Completion',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'completion',
                    'format' => 'paragraph',
                    'blanks' => 'multiple',
                    'suggested_answers' => true,
                ]),
                'scoring_rules' => json_encode(['correct_per_blank' => 1, 'case_sensitive' => false]),
            ],
            [
                'name' => 'Yes/No/Not Given',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'yes_no_not_given',
                    'select_mode' => 'single',
                    'options_fixed' => true,
                    'options' => ['Yes', 'No', 'Not Given'],
                ]),
                'scoring_rules' => json_encode(['correct' => 1, 'incorrect' => 0]),
            ],
            [
                'name' => 'Short Answer',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'short_answer',
                    'word_limit' => '1-3 words',
                    'options' => null,
                ]),
                'scoring_rules' => json_encode(['correct' => 1, 'case_sensitive' => false, 'spelling_check' => true]),
            ],
            [
                'name' => 'Matching Features',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'matching',
                    'left_side' => 'features',
                    'right_side' => 'statements',
                    'select_mode' => 'multiple',
                ]),
                'scoring_rules' => json_encode(['correct' => 1, 'incorrect' => 0]),
            ],
            [
                'name' => 'Matching Sentence Endings',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'matching',
                    'left_side' => 'beginnings',
                    'right_side' => 'endings',
                    'select_mode' => 'multiple',
                ]),
                'scoring_rules' => json_encode(['correct' => 1, 'incorrect' => 0]),
            ],
            [
                'name' => 'Note Completion',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'note_completion',
                    'format' => 'text',
                    'blanks' => 'in_text',
                    'mark_as_blank' => true,
                ]),
                'scoring_rules' => json_encode(['correct_per_blank' => 1, 'case_sensitive' => false]),
            ],
            [
                'name' => 'Table Completion',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'table_completion',
                    'format' => 'table',
                    'blanks' => 'in_cells',
                    'mark_as_blank' => true,
                    'columns' => 'variable',
                ]),
                'scoring_rules' => json_encode(['correct_per_cell' => 1, 'case_sensitive' => false]),
            ],
            [
                'name' => 'Diagram Label Completion',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'diagram_label_completion',
                    'format' => 'image_with_labels',
                    'accepted_file_types' => ['jpg', 'jpeg', 'png'],
                    'mark_as_blank' => true,
                ]),
                'scoring_rules' => json_encode(['correct_per_label' => 1, 'case_sensitive' => false]),
            ],
            [
                'name' => 'Flowchart Completion',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'flowchart_completion',
                    'format' => 'flowchart',
                    'blanks' => 'in_nodes',
                    'mark_as_blank' => true,
                ]),
                'scoring_rules' => json_encode(['correct_per_node' => 1, 'case_sensitive' => false]),
            ],
            [
                'name' => 'Matching Information',
                'module' => 'reading',
                'structure' => json_encode([
                    'type' => 'matching_information',
                    'format' => 'paragraphs_to_statements',
                    'paragraph_count' => 'variable',
                    'select_mode' => 'multiple',
                ]),
                'scoring_rules' => json_encode(['correct' => 1, 'incorrect' => 0]),
            ],
        ];

        foreach ($questionTypes as $type) {
            QuestionType::create([
                'id' => (string) Str::uuid(),
                'name' => $type['name'],
                'module' => $type['module'],
                'structure' => $type['structure'],
                'scoring_rules' => $type['scoring_rules'],
                'is_active' => true,
            ]);
        }
    }
}
