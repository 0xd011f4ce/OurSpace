<?php

namespace App\Types;

class TypeOrderedCollection {
    public $collection;
    public $url;
    public $page_size;

    public function build_response_main ()
    {
        if (!isset ($this->collection) || !isset ($this->url)) {
            return [];
        }

        $total_items = count ($this->collection);
        $total_pages = ceil ($total_items / $this->page_size);

        return [
            "@context" => "https://www.w3.org/ns/activitystreams",
            "id" => $this->url,
            "type" => "OrderedCollection",
            "totalItems" => $total_items,
            "first" => $this->url . "?page=1",
            "last" => $this->url . "?page=" . $total_pages,
        ];
    }

    public function build_response_for_page ($page)
    {
        $total_items = count ($this->collection);
        $total_pages = ceil ($total_items / $this->page_size);
        if ($page > $total_pages) {
            return [];
        }

        $offset = ($page - 1) * $this->page_size;
        $items = array_slice ($this->collection, $offset, $this->page_size);

        return [
            "@context" => "https://www.w3.org/ns/activitystreams",
            "id" => $this->url . "?page=" . $page,
            "type" => "OrderedCollectionPage",
            "partOf" => $this->url,
            "totalItems" => $total_items,
            "orderedItems" => $items,
        ];
    }
}
