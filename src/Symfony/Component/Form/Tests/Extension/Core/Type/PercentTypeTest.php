<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\Core\Type;

use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Intl\Util\IntlTestHelper;

class PercentTypeTest extends TypeTestCase
{
    use ExpectDeprecationTrait;

    public const TESTED_TYPE = PercentType::class;

    private $defaultLocale;

    protected function setUp(): void
    {
        // we test against different locales, so we need the full
        // implementation
        IntlTestHelper::requireFullIntl($this, false);

        parent::setUp();

        $this->defaultLocale = \Locale::getDefault();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \Locale::setDefault($this->defaultLocale);
    }

    public function testSubmitWithRoundingMode()
    {
        $form = $this->factory->create(self::TESTED_TYPE, null, [
            'scale' => 2,
            'rounding_mode' => \NumberFormatter::ROUND_CEILING,
        ]);

        $form->submit('1.23456');

        $this->assertEquals(0.0124, $form->getData());
    }

    public function testSubmitNullUsesDefaultEmptyData($emptyData = '10', $expectedData = 0.1)
    {
        $form = $this->factory->create(static::TESTED_TYPE, null, [
            'empty_data' => $emptyData,
            'rounding_mode' => \NumberFormatter::ROUND_UP,
        ]);
        $form->submit(null);

        $this->assertSame($emptyData, $form->getViewData());
        $this->assertSame($expectedData, $form->getNormData());
        $this->assertSame($expectedData, $form->getData());
    }

    public function testHtml5EnablesSpecificFormatting()
    {
        \Locale::setDefault('de_CH');

        $form = $this->factory->create(static::TESTED_TYPE, null, [
            'html5' => true,
            'rounding_mode' => \NumberFormatter::ROUND_UP,
            'scale' => 2,
            'type' => 'integer',
        ]);
        $form->setData('1234.56');

        $this->assertSame('1234.56', $form->createView()->vars['value']);
        $this->assertSame('number', $form->createView()->vars['type']);
    }

    /**
     * @group legacy
     */
    public function testSubmitWithoutRoundingMode()
    {
        $this->expectDeprecation('Since symfony/form 5.1: Not configuring the "rounding_mode" option is deprecated. It will default to "\NumberFormatter::ROUND_HALFUP" in Symfony 6.0.');

        $form = $this->factory->create(self::TESTED_TYPE, null, [
            'scale' => 2,
        ]);

        $form->submit('1.23456');

        $this->assertEqualsWithDelta(0.0123456, $form->getData(), \PHP_FLOAT_EPSILON);
    }

    /**
     * @group legacy
     */
    public function testSubmitWithNullRoundingMode()
    {
        $this->expectDeprecation('Since symfony/form 5.1: Not configuring the "rounding_mode" option is deprecated. It will default to "\NumberFormatter::ROUND_HALFUP" in Symfony 6.0.');

        $form = $this->factory->create(self::TESTED_TYPE, null, [
            'rounding_mode' => null,
            'scale' => 2,
        ]);

        $form->submit('1.23456');

        $this->assertEqualsWithDelta(0.0123456, $form->getData(), \PHP_FLOAT_EPSILON);
    }
}
