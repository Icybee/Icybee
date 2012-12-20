<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Jobs;

class Job extends \ICanBoogie\ActiveRecord
{
	/**
	 * Identifier of the job.
	 *
	 * @var int
	 */
	public $jobid;

	/**
	 * Identifier of the user that created the job.
	 *
	 * @var int
	 */
	public $uid;

	/**
	 * The job token is used as secret when one wants to execute the job directly.
	 *
	 * @var string
	 */
	public $token;

	/**
	 * Datetime at which the job should the executed.
	 *
	 * If the {@link $periodical} property is not empty, the value is updated, according to the
	 * {@link $periodical} property, after the job has been accomplished.
	 *
	 * @var string
	 */
	public $trigger_at;

	/**
	 * Datetime at which the job should be disposed.
	 *
	 * Note that the job is disposed after is has been executed if its periodical is empty. Only
	 * define {@link $dispose_at} if you which to dispose of a job after a certain time.
	 *
	 * @var string
	 */
	public $dispose_at;

	/**
	 * Modifier for the {@link $trigger_at} property.
	 *
	 * If the {@link $periodical} property is not empty, it is used to update the
	 * {@link $trigger_at} property after a job has been accomplished.
	 *
	 * The periodical can be defined as a number of seconds or a date difference such
	 * as "+1 week".
	 *
	 * @var string
	 */
	public $periodical;

	/**
	 * Serialized worker.
	 *
	 * The job worked is serialized with the {@link serialize()} and {@link base64_encode()}
	 * functions.
	 *
	 * @var string
	 */
	protected $serialized_worker;

	/**
	 * Serialized worker params.
	 *
	 * The worker params are serialized with the {@link serialize()} and {@link base64_encode()}
	 * functions.
	 *
	 * @var string
	 */
	protected $serialized_worker_params;

	/**
	 * Job's worker.
	 *
	 * The worker is a callable. It maybe a function, method or an object implementing
	 * the {@link __invoke()} magic method.
	 *
	 * @var callable
	 */
	private $worker;

	/**
	 * Returns the worker.
	 *
	 * If the worker is not already defined, it is created from the {@link $serialized_worker}
	 * property.
	 *
	 * @return callable|null
	 */
	protected function volatile_get_worker()
	{
		if ($this->worker !== null)
		{
			return $this->worker;
		}

		return $this->serialized_worker ? unserialize(base64_decode($this->serialized_worker)) : null;
	}

	/**
	 * Sets the worker.
	 *
	 * Updates the {@link $worker} property and sets the {@link $serialized_worker} property
	 * to `null`.
	 *
	 * @param unknown_type $worker
	 */
	protected function volatile_set_worker($worker)
	{
		$this->worker = $worker;
		$this->serialized_worker = null;
	}

	/**
	 * Worker params.
	 *
	 * @var array
	 */
	private $worker_params;

	/**
	 * Returns the worker params.
	 *
	 * If the worker params are not already defined, they are created from
	 * the {@link $serialized_worker_params} property.
	 *
	 * @return callable|null
	 */
	protected function volatile_get_worker_params()
	{
		if ($this->worker_params !== null)
		{
			return $this->worker_params;
		}

		return $this->serialized_worker_params ? unserialize(base64_decode($this->serialized_worker_params)) : null;
	}

	/**
	 * Sets the worker params.
	 *
	 * Updates the {@link $worker_params} property and sets the {@link $serialized_worker_params}
	 * property to `null`.
	 *
	 * @param array $params
	 */
	protected function volatile_set_worker_params(array $params)
	{
		$this->worker_params = $params;
		$this->serialized_worker_params = null;
	}

	public function __construct($model='jobs')
	{
		parent::__construct($model);
	}
}