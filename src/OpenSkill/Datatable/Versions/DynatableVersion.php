<?php

namespace OpenSkill\Datatable\Versions;


use OpenSkill\Datatable\Columns\ColumnConfiguration;
use OpenSkill\Datatable\Data\ResponseData;
use OpenSkill\Datatable\Queries\Parser\Datatable19QueryParser;
use OpenSkill\Datatable\Queries\Parser\DynatableQueryParser;
use OpenSkill\Datatable\Queries\Parser\QueryParser;
use OpenSkill\Datatable\Queries\QueryConfiguration;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class DynatableVersion
 * @package OpenSkill\Datatable\Versions
 *
 * Dynatable support. https://www.dynatable.com/
 *
 */
class DynatableVersion extends DatatableVersion
{
    /**
     * DynatableVersion constructor.
     *
     * @param RequestStack $requestStack The current request
     */
    public function __construct(RequestStack $requestStack)
    {
        parent::__construct($requestStack, new DynatableQueryParser());
    }

    /**
     * Is responsible to take the generated data and prepare a response for it.
     * @param ResponseData $data The processed data.
     * @param QueryConfiguration $queryConfiguration the query configuration for the current request.
     * @param ColumnConfiguration[] $columnConfigurations the column configurations for the current data table.
     * @return JsonResponse the response that should be returned to the client.
     */
    public function createResponse(
        ResponseData $data,
        QueryConfiguration $queryConfiguration,
        array $columnConfigurations
    ) {
        $responseData = [
            'records' => $data->data()->toArray(),
            'totalRecordCount' => $data->totalDataCount(),
            'queryRecordCount' => $data->filteredDataCount(),
        ];

        return new JsonResponse($responseData);
    }

    /**
     * @return string The name of the view that this version should use fot the table.
     */
    public function tableView()
    {
        return "datatable::table";
    }

    /**
     * @return string The name of the view that this version should use for the script.
     */
    public function scriptView()
    {
        return "datatable::datatable19";
    }
}
