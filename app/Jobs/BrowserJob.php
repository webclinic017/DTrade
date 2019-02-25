<?php

namespace App\Jobs;

use App\Traits\BrowserScaffold;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Dusk\Browser;

abstract class BrowserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BrowserScaffold;

    protected $user;

    protected $tags;

    protected $tasks;

    protected $debug = false;

    public function __construct(User $user = null, $tags = [])
    {
        $this->user = $user;
        $this->tags = collect($tags);
        $this->tasks = collect([]);
        $this->setup();
        $this->addTag('Browser Job');
        $this->addTaskTags();

        if ($this->debug) {
            \Log::debug("Debug mode activated on Browser Jobs.");
            \Log::debug($this->user);
            \Log::debug($this->tags);
            \Log::debug($this->tasks);
            \Log::debug("Done outputting Browser Job state.\n");
        }
    }

    /**
     * This method is to be overwritten in each Job in order to provide the correct
     * entry point in the construction of a job to add the tasks it will execute before
     * adding the jobs tags.
     */
    abstract public function setup();

    /**
     * Helper function to add a task to the collection of tasks
     * @param BaseTask $task
     */
    private function addTask(BaseTask $task)
    {
        $this->tasks->push($task);
    }

    /**
     * Add tasks to the job, each task must be an instance of BaseTask.
     * @param array|BaseTask $tasks
     */
    public function addTasks($tasks)
    {
        if (is_array($tasks)) {
            foreach ($tasks as $task) {
                $this->addTask($task);
            }
        } else {
            $this->addTask($tasks);
        }
    }

    public function addTag($tag)
    {
        $this->tags->push($tag);
    }

    private function addTaskTags()
    {
        $this->tasks->each(function(BaseTask $task){
            $this->addTag($task->getName());
        });
    }

    public function tags()
    {
        return $this->tags->toArray();
    }

    /**
     * @param User|null $user
     * @throws \Throwable
     */
    public function handle(User $user = null)
    {
        $this->browse(function (Browser $browser) use ($user) {
            if ($this->tasks instanceof Collection) {
                $this->tasks->each(function(BaseTask $task) use ($user, $browser) {
                    if ($this->debug) \Log::debug("Starting '".$task->getName()."'..");
                    $task->run( $browser,$this->user ?: $user);
                    if ($this->debug) \Log::debug("Done with '".$task->getName()."'.");
                    sleep(2);
                });
            }
        });
        $this->closeAll();
    }

    /**
     * This method may be overwritten in each Job in order to provide a callback after executing the job
     */
    public function tearDown(){}

}