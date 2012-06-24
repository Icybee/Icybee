<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord;

use ICanBoogie\ActiveRecord;
use Textmark_Parser;
use Icybee;

class Comment extends ActiveRecord
{
	const COMMENTID = 'commentid';
	const NID = 'nid';
	const PARENTID = 'parentid';
	const UID = 'uid';
	const AUTHOR = 'author';
	const AUTHOR_EMAIL = 'author_email';
	const AUTHOR_URL = 'author_url';
	const AUTHOR_IP = 'author_ip';
	const CONTENTS = 'contents';
	const STATUS = 'status';
	const NOTIFY = 'notify';
	const CREATED = 'created';

	public $commentid;
	public $nid;
	public $parentid;
	public $uid;
	public $author;
	public $author_email;
	public $author_url;
	public $author_ip;
	public $contents;
	public $status;
	public $notify;
	public $created;

	protected function get_node()
	{
		global $core;

		return $core->models['nodes'][$this->nid];
	}

	protected function get_url()
	{
		$node = $this->node;

		return ($node ? $this->node->url : '#unknown-node-' . $this->nid) . '#comment-' . $this->commentid;
	}

	protected function get_absolute_url()
	{
		$node = $this->node;

		return ($node ? $this->node->absolute_url : '#unknown-node-' . $this->nid) . '#comment-' . $this->commentid;
	}

	protected function get_author_icon()
	{
		$hash = md5(strtolower(trim($this->author_email)));

		return 'http://www.gravatar.com/avatar/' . $hash . '.jpg?' . http_build_query
		(
			array
			(
				'd' => 'identicon'
			)
		);
	}

	protected function get_excerpt()
	{
		return $this->excerpt();
	}

	protected function get_is_author()
	{
		return $this->node->uid == $this->uid;
	}

	public function excerpt($limit=55)
	{
		return \ICanBoogie\excerpt((string) $this, $limit);
	}

	public function __toString()
	{
		$str = Textmark_Parser::parse($this->contents);

		return Icybee\Kses::sanitizeComment($str);
	}
}