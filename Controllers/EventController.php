<?php


/**
 * Class EventController
 * @route("/event")
 */
class EventController implements IController
{
    private $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @route("/")
     * @method("get")
     */
    public function Index()
    {
        $parser = new \OData\ODataToSqlQueryParser(Event::class);
        $query = $parser->getSql()['query'];
        $countQuery = $parser->getSql()['countQuery'];
        if ($countQuery != null) {
            return [
                "data" => $this->eventRepository->execute($query, 0),
                "count" => $this->eventRepository->execute($countQuery, 3)
            ];
        } else {
            return $this->eventRepository->execute($query, 0);
        }
    }

    /**
     * @route("/:id")
     * @method("get")
     */
    public function Get($id)
    {
        return $this->eventRepository->get($id);
    }

    /**
     * @route("/add")
     * @method("post")
     */
    public function Add($model)
    {
        return $this->eventRepository->insert($model);
    }

    /**
     * @route("/:id/update")
     * @method("post")
     */
    public function Update($id, $model)
    {
        return $this->eventRepository->update($model);
    }

    /**
     * @route("/:id/delete")
     * @method("post")
     */
    public function Delete($id)
    {
        return $this->eventRepository->delete($id);
    }
}