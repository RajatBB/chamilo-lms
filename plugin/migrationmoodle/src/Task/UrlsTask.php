<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\MigrationMoodle\Task;

use Chamilo\PluginBundle\MigrationMoodle\Extractor\CourseExtractor;
use Chamilo\PluginBundle\MigrationMoodle\Loader\UrlLoader;
use Chamilo\PluginBundle\MigrationMoodle\Transformer\BaseTransformer;
use Chamilo\PluginBundle\MigrationMoodle\Transformer\Property\LoadedCourseLookup;
use Chamilo\PluginBundle\MigrationMoodle\Transformer\Property\LoadedCourseModulesUrlLookup;
use Chamilo\PluginBundle\MigrationMoodle\Transformer\Property\LoadedLpLookup;

/**
 * Class UrlsTask.
 *
 * @package Chamilo\PluginBundle\MigrationMoodle\Task
 */
class UrlsTask extends BaseTask
{
    /**
     * @inheritDoc
     */
    public function getExtractConfiguration()
    {
        return [
            'class' => CourseExtractor::class,
            'query' => "SELECT u.id, u.course, u.name, u.externalurl, cm.section, cm.id cm_id
                FROM mdl_url u
                INNER JOIN mdl_course_modules cm ON (u.course = cm.course AND cm.instance = u.id)
                INNER JOIN mdl_modules m ON cm.module = m.id
                INNER JOIN mdl_course_sections cs ON (cm.course = cs.course AND cm.section = cs.id )
                WHERE m.name = 'url'
                    AND u.course NOT IN (
                        SELECT sco.course
                        FROM mdl_scorm sco
                        INNER JOIN mdl_course_modules cm ON (sco.course = cm.course AND cm.instance = sco.id)
                        INNER JOIN mdl_modules m ON cm.module = m.id
                        INNER JOIN mdl_course_sections cs ON (cm.course = cs.course AND cm.section = cs.id )
                        WHERE m.name = 'scorm'
                    )",
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTransformConfiguration()
    {
        return [
            'class' => BaseTransformer::class,
            'map' => [
                'c_id' => [
                    'class' => LoadedCourseLookup::class,
                    'properties' => ['course'],
                ],
                'lp_id' => [
                    'class' => LoadedLpLookup::class,
                    'properties' => ['section'],
                ],
                'item_id' => [
                    'class' => LoadedCourseModulesUrlLookup::class,
                    'properties' => ['cm_id'],
                ],
                'title' => 'name',
                'url' => 'externalurl',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLoadConfiguration()
    {
        return [
            'class' => UrlLoader::class,
        ];
    }
}
