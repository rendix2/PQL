<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 8. 2. 2019
 * Time: 12:59
 */

namespace BTree;

/**
 * Class BinaryTree
 *
 * @package BTree
 * @author  Tomáš Babický tomas.babicky@websta.de
 */
class BinaryTree
{
    /**
     * @var BinaryTree $left
     */
    private $left;

    /**
     * @var BinaryTree $right
     */
    private $right;

    /**
     * @var mixed $data
     */
    private $data;

    /**
     * BinaryTree constructor.
     *
     * @param BinaryTree $left
     * @param BinaryTree $right
     * @param mixed           $data
     */
    public function __construct(BinaryTree $left, BinaryTree $right, $data)
    {
        $this->left  = $left;
        $this->right = $right;

        $this->data = $data;
    }

    /**
     * BinaryTree destructor.
     */
    public function __destruct()
    {
        $this->destroy($this);
    }

    public function minRec(BinaryTree $root = null)
    {
        if ($root->left === null) {
            return $root;
        }

        $this->minRec($root->left);
    }

    public function min(BinaryTree $root = null)
    {
        while ($root->left !== null) {
            $root = $root->left;
        }

        return $root;
    }

    public function max(BinaryTree $root = null)
    {
        while ($root->right !== null) {
            $root = $root->right;
        }

        return $root;
    }

    public function maxRec(BinaryTree $root = null)
    {
        if ($root->right === null) {
            return $root;
        }

        $this->minRec($root->right);
    }

    public function insert(BinaryTree $root = null, $data)
    {
        if ($root === null) {
           $root = new BinaryTree(null, null, $data);
        }

        if ($data < $root->data) {
            $root->left = $this->insert($root->left, $data);
        } else if ($data > $root->data) {
            $root->right = $this->insert($root->right, $data);
        }

        return $root;
    }

    public function search(BinaryTree $root, $data)
    {
        if ($root === null || $root->data === $data) {
            return $root;
        }

        if ($root->data > $data) {
            return $this->search($root->left, $data);
        } else {
            return $this->search($root->right, $data);
        }
    }

    public function delete(BinaryTree $root = null, $data)
    {
        if ($root === null) {
            return $root;
        }

        if ($root->data > $data) {
            $root->left = $this->delete($root->left, $data);
            return $root;
        } else if ($root->data < $data) {
            $root->right = $this->delete($root->right, $data);
            return $root;
        }

        if ($root->left === null) {
            $tmp = $root->right;
            $root = null;
            return $tmp;
        } else if ($root->right === null) {
            $tmp = $root->left;
            $root = null;
            return $tmp;
        } else {
            $parent = $root->right;
            $succ   = $root->right;

            while ($succ->left !== null) {
                $parent = $succ;
                $succ   = $succ->left;
            }

            $parent->left = $succ->right;
            $root->data   = $succ->data;

            $succ = null;
            return $root;
        }
    }

    public function pre(BinaryTree $root = null)
    {
        if ($root === null) {
            return;
        }

        echo $root->data;

        $this->pre($root->left);
        $this->pre($root->right);
    }

    public function in(BinaryTree $root = null)
    {
        if ($root === null) {
            return;
        }

        $this->in($root->left);
        echo $root->data;
        $this->in($root->right);
    }

    public function post(BinaryTree $root = null)
    {
        if ($root === null) {
            return;
        }

        $this->post($root->left);
        $this->post($root->right);
        echo $root->data;
    }

    private function destroy(BinaryTree $root = null)
    {
        if ($root === null) {
            return;
        }

        $this->post($root->left);
        $this->post($root->right);
        $root = null;
    }
}
