<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\TicketUpdate;
use App\Models\Customer;
use App\Models\IssueSource;
use App\Models\IssueCategory;
use App\Models\Disposition;
use App\Models\Department;
use App\Models\User;
use App\Models\TicketStatus;
use App\Models\SlaLevel;
use Illuminate\Support\Carbon;
use App\Services\Client;
use Illuminate\Support\Arr;

class TicketSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Set the total number you want to seed
        $totalRecords = 520000;
        $batchSize = 100; // Set the desired batch size

        if (Ticket::count() >= $totalRecords) return;

        $totalBatches = ceil($totalRecords / $batchSize);

        for ($batch = 1; $batch <= $totalBatches; $batch++) {
            $start = ($batch - 1) * $batchSize + 1;
            $end = min($batch * $batchSize, $totalRecords);

            $this->seedBatch($start, $end);

            // "Behold, the 'Sleepy Seeder' granting the server some shut-eye between batches, dreaming of data wonders! 😴💤"
            sleep(60);
        }
    }

    /**
     * Seed a batch of records.
     *
     * @param int $start
     * @param int $end
     * @return void
     */
    private function seedBatch($start, $end)
    {
        $faker = Faker::create();

        $randomValue = 5;
        $now = Carbon::now();

        for ($i = $start; $i <= $end; $i++) {

            echo "$i. Creating ticket...\n";

            $randomValue = rand(0, 5);
            $reopened = $randomValue === 5 ? 1 : 0;

            $now = Carbon::now();
            $createdAt = $now->subMinutes(rand(0, 0)); // Subtract a random number of minutes (up to 24 hours)
            $expectedResolutionTime = $createdAt->copy()->addMinutes(rand(60, 1440)); // Add random minutes (1 to 24 hours) to created_at
            $closedAt = $expectedResolutionTime->copy()->addMinutes(rand(10, 1440)); // Add random minutes (10 minutes to 24 hours) to expected_resolution_time
            $resolvedAt = $createdAt->copy()->addMinutes(rand(60, 1440)); // Add random minutes (1 to 24 hours) to created_at

            // Create the ticket
            $ticket = Ticket::create([
                'customer_id' => Customer::inRandomOrder()->first()->id,
                'issue_source_id' => IssueSource::inRandomOrder()->first()->id,
                'issue_category_id' => IssueCategory::inRandomOrder()->first()->id,
                'disposition_id' => Disposition::inRandomOrder()->first()->id,
                'department_id' => Department::inRandomOrder()->first()->id,
                'assigned_to' => User::inRandomOrder()->first()->id,
                'priority' => 0,
                'user_id' => User::inRandomOrder()->first()->id,
                'ticket_status_id' => TicketStatus::inRandomOrder()->first()->id,
                'queue_id' => 1,
                'fcr' => 0,
                'expected_resolution_time' => $expectedResolutionTime,
                'closed_at' => $closedAt,
                'actual_resolution_time' => null,
                'first_response_time' => null,
                'sla_level_id' => SlaLevel::inRandomOrder()->first()->id,
                'creation_email_sent' => 1,
                'post_breach_reminder' => 0,
                'follow_up_reminder' => 0,
                'breached' => 0,
                'from_mail_scanner' => 0,
                // 70% inbound
                'mail_direction' => rand(0, 10) <= 7 ? 1 : 0,
                'subject' => Arr::random([$faker->sentence(), $faker->word(), null]),
                'is_spam' => 0,
                'is_historic' => 0,
                'ticket_source' => 1,
                'first_response_duration' => null,
                'average_handling_time' => null,
                'escalation_times' => null,
                'resolved_at' => $resolvedAt,
                'has_failed_mail_alert' => 0,
                'reopened' => $reopened,
                'user_id' => User::inRandomOrder()->first()->id,
                // 80% 1
                'status' => rand(0, 10) <= 8 ? 1 : 0,
                'created_at' => Carbon::now()->subMinutes(rand(0, 1440 * 90)), // Subtract a random number of minutes (up to 24 hours * x days)
                'updated_at' => Carbon::now(),
            ]);

            echo "Ticket: $ticket->id\n";

            // Generate a random attachment for 30% of the tickets (you can adjust this percentage as needed)
            if (rand(1, 100) <= 30) {
                $this->saveTicketAttachments($ticket);
            }

            $comment = null;
            if (rand(1, 100) <= 75) {
                $comment = $faker->paragraphs(rand(1, 3), true); // Generates 1 to 3 paragraphs
            }

            // Save the ticket update
            $this->saveTicketUpdates(
                $ticket,
                [
                    'previous_ticket_status_id' => 0,
                    'previous_assigned_to' => 0,
                    'previous_department_id' => 0,
                    'department_id' => $ticket->department_id,
                    'comment' => $comment, // Use the generated comment
                    'created_at' => $ticket->created_at,
                    'updated_at' => $ticket->updated_at,

                ],
                null,
            );

            sleep(5);
        }
    }

    protected function saveTicketUpdates($ticket, $data, $comment = null)
    {
        $ticket_log = new TicketUpdate();
        $ticket_log->ticket_id = $ticket->id;
        $ticket_log->user_id = User::inRandomOrder()->first()->id;
        $ticket_log->comment = $comment;
        $ticket_log->assigned_to = $ticket->assigned_to;
        $ticket_log->previous_ticket_status_id = $data['previous_ticket_status_id'] ?? 0;
        $ticket_log->previous_assigned_to = $data['previous_assigned_to'] ?? 0;
        $ticket_log->previous_department_id = $data['previous_department_id'] ?? 0;
        $ticket_log->department_id = $data['department_id'] ?? 0;
        $ticket_log->ticket_status_id = $ticket->ticket_status_id;
        $ticket_log->status = 1;
        $ticket_log->save();

        if ($ticket->ticket_status_id == TicketStatus::RESOLVED) {
            $resolved_time = Carbon::now()->toDateTimeString();
            $ticket->actual_resolution_time = $resolved_time;
            if (!$ticket->resolved_at)
                $ticket->resolved_at = Carbon::now();
        }

        if ($ticket->ticket_status_id == TicketStatus::CLOSED) {
            $closed_time = Carbon::now()->toDateTimeString();
            $ticket->closed_at = $closed_time;
        }
        $ticket->save();

        return $ticket_log;
    }

    /**
     * Get attachments for a ticket and return the file path.
     *
     * @param int $ticketId
     * @param string $attachmentType
     * @return string|null
     */
    private function saveTicketAttachments($ticket)
    {
        $randomNumber = rand(1, 100); // Generate a random number between 1 and 100.

        if ($randomNumber <= 50) {
            // Generate 1 attachment
            $this->generateAttachment($ticket);
        } elseif ($randomNumber <= 70) {
            // Generate 2 attachments
            $this->generateAttachment($ticket);
            $this->generateAttachment($ticket);
        } elseif ($randomNumber <= 80) {
            // Generate 3 attachments
            $this->generateAttachment($ticket);
            $this->generateAttachment($ticket);
            $this->generateAttachment($ticket);
        } else {
            // Generate 4 attachments
            $this->generateAttachment($ticket);
            $this->generateAttachment($ticket);
            $this->generateAttachment($ticket);
            $this->generateAttachment($ticket);
        }

        return true;
    }

    /**
     * Generate a single attachment for the ticket and save it.
     *
     * @param int $ticketId
     * @param string $attachmentType
     * @return void
     */
    private function generateAttachment($ticket)
    {
        // Get the current date using Carbon
        $now = Carbon::now();

        // Define the directory where the attachments will be saved
        $attachmentFolder = 'tickets/' . $now->year . '/' . $now->month . '/' . $now->day;

        $attachmentFilename = 'ticket_' . $ticket->id . '_' . now()->format('Ymd_His') . '.jpg'; // Use the desired file extension

        $dimensions = Arr::random([
            '200/300', '300/400', '200', '300', '400', '500', '700', '1200',
            '800/600', '600/800', '800', '600', '1000', '900', '1024/768',
            '1600/900', '1920/1080', '1280/720', '1366/768'
        ]);

        Client::downloadFileFromUrl('https://picsum.photos/' . $dimensions, $attachmentFolder, $attachmentFilename, $ticket);
    }
}
