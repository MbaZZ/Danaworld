<?php
interface list_serviceInterface{
	function displayList();
	function sortList($key, $order);
	function deleteElemList($key);
	function selectElemList($key);
	function dragElemList($elemKey);
	function dropElemList($elemKey, $destKey);
}
?>
