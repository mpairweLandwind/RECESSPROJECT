protected function schedule(Schedule $schedule)
{
    $schedule->command('send:challengereports')->daily();
}
