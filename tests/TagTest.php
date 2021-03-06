<?php

use PHPUnit\Framework\TestCase;

/**
 * Class TagTest
 */
class TagTest extends TestCase
{
    public static $config = [
        'url' => '',
        'login' => '',
        'password' => '',
        'customerKey' => '',
        'token' => '',
    ];

    /**
     * @var iPresso
     */
    private $class;

    /**
     * TagTest constructor.
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     * @throws Exception
     */
    public function __construct(string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->class = (new iPresso())
            ->setLogin(self::$config['login'])
            ->setPassword(self::$config['password'])
            ->setCustomerKey(self::$config['customerKey'])
            ->setToken(self::$config['token'])
            ->setUrl(self::$config['url']);
    }

    public function testTagClass()
    {
        $this->assertInstanceOf(\iPresso\Service\TagService::class, $this->class->tag);
    }

    /**
     * @throws Exception
     */
    public function testTagAddWrong()
    {
        $tag = new \iPresso\Model\Tag();

        $this->expectException(Exception::class);
        $tag->getTag();
    }

    /**
     * @depends testTagClass
     */
    public function testTagGetAll()
    {
        $response = $this->class->tag->get();

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertEquals(\iPresso\Service\Response::STATUS_OK, $response->getCode());

        $this->assertObjectHasAttribute('tag', $response->getData());
    }

    /**
     * @return integer
     * @throws Exception
     */
    public function testTagAdd()
    {
        $tag = new \iPresso\Model\Tag();
        $tag->setName('Unit tests');
        $response = $this->class->tag->add($tag);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_CREATED, \iPresso\Service\Response::STATUS_FOUND]);

        $this->assertObjectHasAttribute('tag', $response->getData());

        $this->assertGreaterThan(0, $response->getData()->tag->id);

        return (integer)$response->getData()->tag->id;
    }

    /**
     * @depends testTagAdd
     * @param int $idTag
     * @return int
     * @throws Exception
     */
    public function testTagGet(int $idTag)
    {
        $this->assertGreaterThan(0, $idTag);

        $response = $this->class->tag->get($idTag);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_OK]);

        $this->assertObjectHasAttribute('tag', $response->getData());

        return $idTag;
    }

    /**
     * @depends testTagGet
     * @param int $idTag
     * @throws Exception
     * @return int
     */
    public function testTagEdit(int $idTag)
    {
        $this->assertGreaterThan(0, $idTag);

        $tag = new \iPresso\Model\Tag();
        $tag->setName('Unit tests edition');

        $response = $this->class->tag->edit($idTag, $tag);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_OK]);

        $this->assertTrue($response->getData());

        return $idTag;
    }

    /**
     * @return integer
     * @throws Exception
     */
    public function testContactAdd()
    {
        $contact = new \iPresso\Model\Contact();
        $contact->setEmail('michal.per+test@encja.com');

        $response = $this->class->contact->add($contact);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_OK]);

        $this->assertObjectHasAttribute('contact', $response->getData());

        $contact = reset($response->getData()->contact);

        $this->assertContains($contact->code, [\iPresso\Service\Response::STATUS_CREATED, \iPresso\Service\Response::STATUS_FOUND, \iPresso\Service\Response::STATUS_SEE_OTHER]);

        $this->assertGreaterThan(0, $contact->id);

        return (integer)$contact->id;
    }

    /**
     * @depends testTagAdd
     * @depends testContactAdd
     * @param int $idTag
     * @param int $idContact
     * @throws Exception
     */
    public function testAddContactToTag(int $idTag, int $idContact)
    {
        $this->assertGreaterThan(0, $idTag);
        $this->assertGreaterThan(0, $idContact);

        $response = $this->class->tag->addContact($idTag, [$idContact]);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_CREATED]);
    }

    /**
     * @depends testTagAdd
     * @depends testContactAdd
     * @depends testAddContactToTag
     * @param int $idTag
     * @param int $idContact
     * @throws Exception
     */
    public function testGetContactTag(int $idTag, int $idContact)
    {
        $this->assertGreaterThan(0, $idTag);
        $this->assertGreaterThan(0, $idContact);

        $response = $this->class->tag->getContact($idTag);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_OK]);

        $this->assertObjectHasAttribute('id', $response->getData());

        $this->assertContains($idContact, $response->getData()->id);
    }

    /**
     * @depends testTagAdd
     * @depends testContactAdd
     * @depends testAddContactToTag
     * @param int $idTag
     * @param int $idContact
     * @throws Exception
     */
    public function testDeleteContactTag(int $idTag, int $idContact)
    {
        $this->assertGreaterThan(0, $idTag);
        $this->assertGreaterThan(0, $idContact);

        $response = $this->class->tag->deleteContact($idTag, $idContact);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_OK]);
    }

    /**
     * @depends testTagAdd
     * @depends testContactAdd
     * @depends testDeleteContactTag
     * @param int $idTag
     * @param int $idContact
     * @throws Exception
     */
    public function testCheckContactHasTagAfterDelete(int $idTag, int $idContact)
    {
        $this->assertGreaterThan(0, $idTag);
        $this->assertGreaterThan(0, $idContact);

        $response = $this->class->tag->getContact($idTag);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_OK]);

        $this->assertObjectHasAttribute('count', $response->getData());

        if ($response->getData()->count > 0) {
            $this->assertObjectHasAttribute('id', $response->getData());

            $this->assertNotContains($idContact, $response->getData()->id);
        }
    }

    /**
     * @depends testTagAdd
     * @param int $idTag
     * @throws Exception
     */
    public function testTagDelete(int $idTag)
    {
        $this->assertGreaterThan(0, $idTag);

        $response = $this->class->tag->delete($idTag);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_OK]);
    }

    /**
     * @depends testContactAdd
     * @param int $idContact
     * @return string
     * @throws Exception
     */
    public function testContactAddTag(int $idContact)
    {
        $tag = 'Unit tests';

        $this->assertGreaterThan(0, $idContact);

        $response = $this->class->contact->addTag($idContact, $tag);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_CREATED]);

        return $tag;
    }

    /**
     * @depends testContactAdd
     * @depends testContactAddTag
     * @param int $idContact
     * @param string $tag
     * @return integer
     * @throws Exception
     */
    public function testContactGetTag(int $idContact, string $tag)
    {
        $this->assertGreaterThan(0, $idContact);

        $response = $this->class->contact->getTag($idContact);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_OK]);

        $this->assertObjectHasAttribute('tag', $response->getData());

        $idTag = array_search($tag, (array)$response->getData()->tag);

        $this->assertGreaterThan(0, $idTag);

        return $idTag;
    }

    /**
     * @depends testContactAdd
     * @depends testContactGetTag
     * @param int $idContact
     * @param int $idTag
     * @throws Exception
     */
    public function testContactDeleteTag(int $idContact, int $idTag)
    {
        $this->assertGreaterThan(0, $idContact);
        $this->assertGreaterThan(0, $idTag);

        $response = $this->class->contact->deleteTag($idContact, $idTag);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_OK]);

        $response = $this->class->tag->delete($idTag);

        $this->assertInstanceOf(\iPresso\Service\Response::class, $response);

        $this->assertContains($response->getCode(), [\iPresso\Service\Response::STATUS_OK]);
    }
}