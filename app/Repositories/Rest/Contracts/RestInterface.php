<?php namespace App\Repositories\Rest\Contracts;

interface RestInterface
{
    public function all();

    public function paginate($limit);

    public function create(array $data);

    public function createMany(array $data);

    public function update(array $data, $id);

    public function delete($id);

    public function show($id);
}
