<?php
/**
* @version $Id$
* Kunena Component
* @package Kunena
*
* @Copyright (C) 2008 - 2009 Kunena Team All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.kunena.com
*
* Based on FireBoard Component
* @Copyright (C) 2006 - 2007 Best Of Joomla All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.bestofjoomla.com
*
* Based on Joomlaboard Component
* @copyright (C) 2000 - 2004 TSMF / Jan de Graaff / All Rights Reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author TSMF & Jan de Graaff
**/

defined( '_JEXEC' ) or die('Restricted access');

$fbConfig =& CKunenaConfig::getInstance();
global $is_Moderator;
//Modify this to change the minimum time between karma modifications from the same user
$karma_min_seconds = '14400'; // 14400 seconds = 6 hours
?>

<table border = 0 cellspacing = 0 cellpadding = 0 width = "100%" align = "center">
    <tr>
        <td>
            <br>
            <center>
                <?php
                //I hope these are needed :)
                $catid = (int)$catid;
                $pid = (int)$pid;

                //This checks:
                // - if the karma function is activated by the admin
                // - if a registered user submits the modify request
                // - if he specifies an action related to the karma change
                // - if he specifies the user that will have the karma modified
                if ($fbConfig->showkarma && $kunena_my->id != "" && $kunena_my->id != 0 && $do != '' && $userid != '')
                {
                    $time = CKunenaTools::fbGetInternalTime();

                    if ($kunena_my->id != $userid)
                    {
                        // This checkes to see if it's not too soon for a new karma change
                        if (!$is_Moderator)
                        {
                            $kunena_db->setQuery("SELECT karma_time FROM #__fb_users WHERE userid='{$kunena_my->id}'");
                            $karma_time_old = $kunena_db->loadResult();
                            $karma_time_diff = $time - $karma_time_old;
                        }

                        if ($is_Moderator || $karma_time_diff >= $karma_min_seconds)
                        {
                            if ($do == "increase")
                            {
                                $kunena_db->setQuery('UPDATE #__fb_users SET karma_time=' . $time . ' WHERE userid=' . $kunena_my->id . '');
							    $kunena_db->query() or trigger_dberror("Unable to update karma.");
							    $kunena_db->setQuery('UPDATE #__fb_users SET karma=karma+1 WHERE userid=' . $userid . '');
							    $kunena_db->query() or trigger_dberror("Unable to update karma.");
							    echo _KARMA_INCREASED . '<br /> <a href="' . JRoute::_(KUNENA_LIVEURLREL . '&amp;func=view&amp;catid=' . $catid . '&amp;id=' . $pid) . '">' . _POST_CLICK . '</a>.';
								if ($pid) {
                                	echo CKunenaLink::GetAutoRedirectHTML(JRoute::_(KUNENA_LIVEURLREL.'&amp;func=view&amp;catid='.$catid.'&id='.$pid), 3500);
								} else {
                                	echo CKunenaLink::GetAutoRedirectHTML(JRoute::_(KUNENA_PROFILE_LINK_SUFFIX.$userid), 3500);
                                }
                            }
                            else if ($do == "decrease")
                            {
                                $kunena_db->setQuery('UPDATE #__fb_users SET karma_time=' . $time . ' WHERE userid=' . $kunena_my->id . '');
                                $kunena_db->query() or trigger_dberror("Unable to update karma.");
                                $kunena_db->setQuery('UPDATE #__fb_users SET karma=karma-1 WHERE userid=' . $userid . '');
                                $kunena_db->query() or trigger_dberror("Unable to update karma.");
                                echo _KARMA_DECREASED . '<br /> <a href="' . JRoute::_(KUNENA_LIVEURLREL. '&amp;func=view&amp;catid=' . $catid . '&amp;id=' . $pid) . '">' . _POST_CLICK . '</a>.';
								if ($pid) {
                                	echo CKunenaLink::GetAutoRedirectHTML(JRoute::_(KUNENA_LIVEURLREL.'&amp;func=view&amp;catid='.$catid.'&id='.$pid), 3500);
								} else {
                                	echo CKunenaLink::GetAutoRedirectHTML(JRoute::_(KUNENA_PROFILE_LINK_SUFFIX.$userid), 3500);
                                }
                            }
                            else
                            { //you got me there... don't know what to $do
                                echo _USER_ERROR_A;
                                echo _USER_ERROR_B . "<br /><br />";
                                echo _USER_ERROR_C . "<br /><br />" . _USER_ERROR_D . ": <code>fb001-karma-02NoDO</code><br /><br />";
                            }
                        }
                        else
                            echo _KARMA_WAIT . '<br /> ' . _KARMA_BACK . ' <a href="' . JRoute::_(KUNENA_LIVEURLREL . '&amp;func=view&amp;catid=' . $catid . '&amp;id=' . $pid) . '">' . _POST_CLICK . '</a>.';
                    }
                    else if ($kunena_my->id == $userid) // In case the user tries modifing his own karma by changing the userid from the URL...
                    {
                        if ($do == "increase")   // Seriously decrease his karma if he tries to increase it
                        {
                            $kunena_db->setQuery('UPDATE #__fb_users SET karma=karma-10, karma_time=' . $time . ' WHERE userid=' . $kunena_my->id . '');
                            $kunena_db->query() or trigger_dberror("Unable to update karma.");
							echo _KARMA_SELF_INCREASE . '<br />' . _KARMA_BACK . ' <a href="' . JRoute::_(KUNENA_LIVEURLREL . '&amp;func=view&amp;catid=' . $catid . '&amp;id=' . $pid) . '">' . _POST_CLICK . '</a>.';
                        }

                        if ($do == "decrease") // Stop him from decreasing his karma but still update karma_time
                        {
                            $kunena_db->setQuery('UPDATE #__fb_users SET karma_time=' . $time . ' WHERE userid=' . $kunena_my->id . '');
                            $kunena_db->query() or trigger_dberror("Unable to update karma.");
                            echo _KARMA_SELF_DECREASE . '<br /> ' . _KARMA_BACK . ' <a href="' . JRoute::_(KUNENA_LIVEURLREL . '&amp;func=view&amp;catid=' . $catid . '&amp;id=' . $pid) . '">' . _POST_CLICK . '</a>.';
                        }
                    }
                }
                else
                { //get outa here, you fraud!
                    echo _USER_ERROR_A;
                    echo _USER_ERROR_B . "<br /><br />";
                    echo _USER_ERROR_C . "<br /><br />" . _USER_ERROR_D . ": <code>fb001-karma-01NLO</code><br /><br />";
                //that should scare 'em off enough... ;-)
                }
                ?>
            </center>
        </td>
    </tr>
</table>
