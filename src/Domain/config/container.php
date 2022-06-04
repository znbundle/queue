<?php

return [
	'singletons' => [
        'ZnBundle\\Queue\\Domain\\Interfaces\\Services\\JobServiceInterface' => 'ZnBundle\\Queue\\Domain\\Services\\JobService',
        'ZnBundle\\Queue\\Domain\\Interfaces\\Repositories\\JobRepositoryInterface' => 'ZnBundle\\Queue\\Domain\\Repositories\\Eloquent\\JobRepository',
		'ZnBundle\\Queue\\Domain\\Interfaces\\Services\\ScheduleServiceInterface' => 'ZnBundle\\Queue\\Domain\\Services\\ScheduleService',
		'ZnBundle\\Queue\\Domain\\Interfaces\\Repositories\\ScheduleRepositoryInterface' => 'ZnBundle\\Queue\\Domain\\Repositories\\Eloquent\\ScheduleRepository',
	],
	'entities' => [
		'ZnBundle\\Queue\\Domain\\Entities\\ScheduleEntity' => 'ZnBundle\\Queue\\Domain\\Interfaces\\Repositories\\ScheduleRepositoryInterface',
		'ZnBundle\\Queue\\Domain\\Entities\\JobEntity' => 'ZnBundle\\Queue\\Domain\\Interfaces\\Repositories\\JobRepositoryInterface',
	],
];