<?php

namespace OpenSkill\Datatable\Queries\Parser;


use OpenSkill\Datatable\Columns\ColumnConfiguration;
use OpenSkill\Datatable\DatatableException;
use OpenSkill\Datatable\Queries\QueryConfiguration;
use OpenSkill\Datatable\Queries\QueryConfigurationBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class DynatableQueryParser extends QueryParser
{

    /**
     * Method to determine if this parser can handle the query parameters. If so then the parser should return true
     * and be able to return a DTQueryConfiguration
     *
     * @param Request $request The current request, that should be investigated
     * @return bool true if the parser is able to parse the query parameters and to return a DTQueryConfiguration
     */
    public function canParse(Request $request)
    {
        return $request->query->has("page") && $request->query->has("perPage");
    }

    /**
     * Method that should parse the request and return a DTQueryConfiguration
     *
     * @param Request $request The current request that should be investigated
     * @param ColumnConfiguration[] $columnConfiguration The configuration of the columns
     * @return QueryConfiguration the configuration the provider can use to prepare the data
     */
    public function parse(Request $request, array $columnConfiguration)
    {
        $query = $request->query;
        $builder = QueryConfigurationBuilder::create();

        $this->getStart($query, $builder);

        $this->getLength($query, $builder);

        $this->getSearch($query, $builder);

        $this->determineSortableColumns($query, $builder, $columnConfiguration);

        return $builder->build();
    }

    /**
     * Helper function that will check if a variable is empty
     * @param mixed $string
     * @return bool true if empty, false otherwise
     */
    private function isEmpty($string)
    {
        return empty($string);
    }

    /**
     * Helper function that will check if a variable has a value
     *
     * NOTE: (this is almost the opposite of isEmpty, but it is *not* the same)
     *
     * @param mixed $string
     * @return bool true if empty, false otherwise
     */
    private function hasValue($string)
    {
        return isset($string) && (strlen($string) > 0);
    }

    /**
     * @param ParameterBag $query
     * @param QueryConfigurationBuilder $builder
     */
    public function getStart($query, $builder)
    {
        if ($query->has('offset')) {
            $builder->start($query->get('offset'));
        }
    }

    /**
     * @param ParameterBag $query
     * @param QueryConfigurationBuilder $builder
     */
    public function getLength($query, $builder)
    {
        if ($query->has('perPage')) {
            $builder->length($query->get('perPage'));
        }
    }

    /**
     * @param ParameterBag $query
     * @param QueryConfigurationBuilder $builder
     */
    public function getSearch($query, $builder)
    {
        if ($query->has('queries')) {
            $queries = $query->get('queries');

            if (isset($queries['search']) && $this->hasValue($queries['search'])) {
                $builder->searchValue($queries['search']);
            }
        }
    }

    /**
     * @param ParameterBag $query
     * @param QueryConfigurationBuilder $builder
     * @param ColumnConfiguration[] $columnConfiguration
     * @throws DatatableException when a column for sorting is out of bounds
     * @return bool success?
     */
    private function determineSortableColumns($query, $builder, array $columnConfiguration)
    {
        if (!$query->has("sorts")) {
            return false;
        }

        $columns = $query->get('sorts');

        foreach($columns as $name => $dir) {
            $direction = ($dir == 1 ? 'asc' : 'desc');
            $this->addColumnToOrdering($builder, $columnConfiguration, $name, $direction);
        }

        return true;
    }

    /**
     * @param QueryConfigurationBuilder $builder
     * @param ColumnConfiguration[] $columnConfiguration
     * @param $columnName the name of a column that needs ordering
     * @throws DatatableException when a column for sorting does not exist
     * @return bool success?
     */
    private function addColumnToOrdering($builder, array $columnConfiguration, $columnName, $columnDirection)
    {
        foreach($columnConfiguration as $column) {
            if ($column->getName() == $columnName) {
                $builder->columnOrder($columnName, $columnDirection);

                return;
            }
        }

        throw new DatatableException('Column to order does not exist.');
    }
}
