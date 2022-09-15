<?php

namespace App\Jobs;

use App\Events\JobFinished;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $user;

    protected $tags;

    protected $tasks;

    protected $debug = false;

    public $timeout = 180;

    public function __construct(User $user = null, $tags = [])
    {
        $this->user = $user;
        $this->tags = collect($tags);
        $this->tasks = collect([]);
        $this->setup();
        $namespace = __NAMESPACE__.'\\';
        $className = get_class();
        $name = str_replace($namespace, '', $className);
        $this->addTag($name);
        $this->addTaskTags();

        if ($this->debug) {
            \Log::debug('Debug mode activated on Browser Jobs.');
            \Log::debug($this->user);
            \Log::debug($this->tags);
            \Log::debug($this->tasks);
            \Log::debug("Done outputting Browser Job state.\n");
        }

        event(new \App\Events\JobStarted($this->toString()));
    }

    public function toString()
    {
        $concreteClass = get_class($this);
        $tags = implode(', ', $this->tags());

        return "Job '$concreteClass' with tags '$tags'";
    }

    /**
     * This method is to be overwritten in each Job in order to provide the correct
     * entry point in the construction of a job to add the tasks it will execute before
     * adding the jobs tags.
     */
    abstract public function setup();

    /**
     * Helper function to add a task to the collection of tasks.
     *
     * @param BaseTask $task
     */
    private function addTask(BaseTask $task)
    {
        $this->tasks->push($task);
    }

    /**
     * Add tasks to the job, each task must be an instance of BaseTask.
     *
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
        $this->tasks->each(function (BaseTask $task) {
            $this->addTag($task->getName());
        });
    }

    public function tags()
    {
        return $this->tags->toArray();
    }

    /**
     * @param User|null $user
     *
     * @throws \Throwable
     */
    public function handle(User $user = null)
    {
        if ($this->tasks instanceof Collection) {
            $this->tasks->each(function (BaseTask $task) use ($user) {
                if ($this->debug) {
                    \Log::debug("Starting '".$task->getName()."'..");
                }
                $task->run($this->user ?: $user);
                if ($this->debug) {
                    \Log::debug("Done with '".$task->getName()."'.");
                }
                sleep(2);
            });
        }
        event(new JobFinished($this->toString()));
        $this->tearDown();
    }

    /**
     * This method may be overwritten in each Job in order to provide a callback after executing the job.
     */
    public function tearDown()
    {
    }
}
