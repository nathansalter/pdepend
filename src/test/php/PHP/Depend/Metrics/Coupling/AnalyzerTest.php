<?php
/**
 * This file is part of PHP_Depend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2011, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Metrics
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2011 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pdepend.org/
 */

require_once dirname(__FILE__) . '/../AbstractTest.php';

/**
 * Test case for the coupling analyzer.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Metrics
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2011 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 *
 * @covers PHP_Depend_Metrics_Coupling_Analyzer
 * @group pdepend
 * @group pdepend::metrics
 * @group pdepend::metrics::coupling
 * @group unittest
 */
class PHP_Depend_Metrics_Coupling_AnalyzerTest extends PHP_Depend_Metrics_AbstractTest
{
    /**
     * testGetNodeMetricsReturnsAnEmptyArrayByDefault
     *
     * @return void
     */
    public function testGetNodeMetricsReturnsAnEmptyArrayByDefault()
    {
        $analyzer = new PHP_Depend_Metrics_Coupling_Analyzer();
        $node     = $this->getMock('PHP_Depend_Code_NodeI');

        self::assertEquals(array(), $analyzer->getNodeMetrics($node));
    }

    /**
     * testGetNodeMetricsReturnsArrayWithExpectedKeys
     * 
     * @return void
     */
    public function testGetNodeMetricsReturnsArrayWithExpectedKeys()
    {
        $packages = self::parseTestCaseSource(__METHOD__);

        $class = $packages->current()
            ->getClasses()
            ->current();

        $analyzer = new PHP_Depend_Metrics_Coupling_Analyzer();
        $analyzer->analyze($packages);

        $metrics = array_keys($analyzer->getNodeMetrics($class));
        sort($metrics);

        self::assertEquals(array('cbo', 'ce'), $metrics);
    }

    /**
     * testAnalyzerGetProjectMetricsReturnsArrayWithExpectedKeys
     *
     * @return void
     */
    public function testAnalyzerGetProjectMetricsReturnsArrayWithExpectedKeys()
    {
        $analyzer = new PHP_Depend_Metrics_Coupling_Analyzer();
        $analyzer->analyze(self::parseTestCaseSource(__METHOD__));

        $metrics = array_keys($analyzer->getProjectMetrics());
        sort($metrics);

        self::assertEquals(array('calls', 'fanout'), $metrics);
    }

    /**
     * Tests that the analyzer calculates correct fanout and call metrics for
     * functions.
     *
     * @return void
     */
    public function testAnalyzerCalculatesCorrectFunctionCoupling()
    {
        $packages = self::parseTestCaseSource(__METHOD__);

        $analyzer = new PHP_Depend_Metrics_Coupling_Analyzer();
        $analyzer->analyze($packages);

        $project = $analyzer->getProjectMetrics();

        self::assertEquals(array('calls' => 10, 'fanout' => 7), $project);
    }

    /**
     * Tests that the analyzer calculates correct fanout and call metrics for
     * methods.
     *
     * @return void
     */
    public function testAnalyzerCalculatesCorrectMethodCoupling()
    {
        $analyzer = new PHP_Depend_Metrics_Coupling_Analyzer();
        $analyzer->analyze(self::parseTestCaseSource(__METHOD__));

        $project = $analyzer->getProjectMetrics();

        self::assertEquals(array('calls' => 10, 'fanout' => 9), $project);
    }

    /**
     * Tests that the analyzer calculates correct fanout and call metrics for
     * properties.
     *
     * @return void
     */
    public function testAnalyzerCalculatesCorrectPropertyCoupling()
    {
        $analyzer = new PHP_Depend_Metrics_Coupling_Analyzer();
        $analyzer->analyze(self::parseTestCaseSource(__METHOD__));

        $project = $analyzer->getProjectMetrics();

        self::assertEquals(array('calls' => 0, 'fanout' => 3), $project);
    }

    /**
     * Tests that the analyzer calculates correct fanout and call metrics for
     * properties.
     *
     * @return void
     */
    public function testAnalyzerCalculatesCorrectClassCoupling()
    {
        $analyzer = new PHP_Depend_Metrics_Coupling_Analyzer();
        $analyzer->analyze(self::parseTestCaseSource(__METHOD__));

        $project = $analyzer->getProjectMetrics();

        self::assertEquals(array('calls' => 10, 'fanout' => 12), $project);
    }

    /**
     * Tests that the analyzer calculates correct fanout and call metrics for
     * complete source.
     *
     * @return void
     */
    public function testAnalyzerCalculatesCorrectCoupling()
    {
        $analyzer = new PHP_Depend_Metrics_Coupling_Analyzer();
        $analyzer->analyze(self::parseSource('metrics/Coupling/Project'));

        $project = $analyzer->getProjectMetrics();

        self::assertEquals(array('calls' => 30, 'fanout' => 31), $project);
    }

    /**
     * Tests that the analyzer calculates the expected call count.
     *
     * @param string  $fileName File with test source.
     * @param integer $calls    Number of expected calls.
     * @param integer $fanout   Expected fanout value.
     *
     * @return void
     * @dataProvider dataProviderAnalyzerCalculatesExpectedCallCount
     */
    public function testAnalyzerCalculatesExpectedCallCount($fileName, $calls, $fanout)
    {
        $analyzer = new PHP_Depend_Metrics_Coupling_Analyzer();
        $analyzer->analyze(self::parseTestCaseSource($fileName));

        $project = $analyzer->getProjectMetrics();

        self::assertEquals(array('calls' => $calls, 'fanout' => $fanout), $project);
    }

    /**
     * Data provider that returns different test files and the corresponding
     * invocation count value.
     *
     * @return array
     */
    public static function dataProviderAnalyzerCalculatesExpectedCallCount()
    {
        return array(
            array(__METHOD__ . '#01', 0, 0),
            array(__METHOD__ . '#02', 0, 0),
            array(__METHOD__ . '#03', 0, 0),
            array(__METHOD__ . '#04', 1, 0),
            array(__METHOD__ . '#05', 1, 0),
            array(__METHOD__ . '#06', 2, 0),
            array(__METHOD__ . '#07', 1, 0),
            array(__METHOD__ . '#08', 1, 0),
            array(__METHOD__ . '#09', 1, 0),
            array(__METHOD__ . '#10', 2, 0),
            array(__METHOD__ . '#11', 2, 0),
            array(__METHOD__ . '#12', 1, 1),
            array(__METHOD__ . '#13', 0, 1),
            array(__METHOD__ . '#14', 0, 1),
            array(__METHOD__ . '#15', 1, 1),
            array(__METHOD__ . '#16', 2, 1),
            array(__METHOD__ . '#17', 4, 2),
            array(__METHOD__ . '#18', 1, 0),
            array(__METHOD__ . '#19', 1, 1),
        );
    }
}