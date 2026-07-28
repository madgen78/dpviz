# Dial Plan Vizualizer (dpviz)

## Overview
The **Dial Plan Vizualizer** (dpviz) is a module for [FreePBX®](http://www.freepbx.org/), an open-source graphical user interface for managing [Asterisk](http://www.asterisk.org/) phone systems. FreePBX is licensed under GPL.

This module draws the call flow for any inbound route as an interactive diagram, showing where a call goes at each step. That makes it easier to troubleshoot routing problems, tidy up a dial plan, and document an existing system.

It is particularly useful for:
- **Understanding call distribution** – "Which extensions ring when someone calls X?"
- **Tracing call logic** – "When a call comes in on Y, does it go directly to the IVR, or are Time Conditions applied first?"
- **Identifying misconfigurations** – Quickly spot and correct unintended call routing behaviors.
- **Streamlining PBX management** – Reduce the time spent manually tracking call flows in complex systems.

## Installation & Upgrade

#### Install or Upgrade via Command Line:
```sh
fwconsole ma downloadinstall https://github.com/madgen78/dpviz/archive/refs/heads/main.zip
```

#### Install or Upgrade via Web GUI
**FreePBX, TangoPBX, IncrediblePBX users**: **Log into PBX**, then navigate to **Admin > Module Admin > Module Updates tab**.

**PBXAct**: **Log into PBXAct users**, then navigate to **Modules > Updates > Module Updates tab**.
 
1. Click **Upload Modules**.
2. **Download (From Web)** Enter ```https://github.com/madgen78/dpviz/archive/refs/heads/main.zip``` then click **Download (From Web)**.
- OR
2. **Download the module** from the following link: [Download dpviz](https://github.com/madgen78/dpviz/archive/refs/heads/main.zip).
    - Set the type to **"Upload (From Hard Disk)"**.
    - Click **Choose File**, select the downloaded module, then click **Upload (From Hard Disk)**.
3. After the download or upload completes, click **Local Module Administration**.
4. Scroll down to **Dial Plan Vizualizer** under the **Reports** section and click on it to expand.
5. Click **Install** or **Upgrade to -version- and Enable** and then click **Process** (at the bottom of page) to complete the installation.


## Automatic Updates

If **Automatic Module Updates** is enabled, future updates to `dpviz` will be installed automatically during the scheduled update window.

If it is **disabled**, you can either check for updates manually or schedule them using `crontab` as the root user:

`crontab -e` 

Add the following line to check for and install updates every Saturday at 10 AM:

`0  10 * * 6 fwconsole ma upgrade dpviz >> /root/dpviz.log 2>&1` 

You may adjust the schedule as needed.

## Usage
1. **Log in to your PBX** and navigate to **Reports > Dial Plan Vizualizer**.
2. **Select or search for an Inbound Route, Time Condition, Call Flow, IVR, Queue, Ring Group, Dynamic Route, Announcement, Language, Misc Application, or Extension** using the dropdown menu.
3. **Labels** are placed on the right (vertical) or above (horizontal) the paths drawn.
4. **Registration Status** is shown by the node border color: **green** (online), **red** (offline), or **white** (virtual or non-extension).
5. **Asterisk DND or Call Forward** status is shown by the node border color: **yellow** (enabled).
6. **Queue paused state** is shown with a ⏸️ in the edge text(single) or next to the extension(combine).
7. **Dynamic queue member login status** is shown by the node border color: **blue**(logged in).
8. **Active call path** is shown by the arrow color on Time Conditions and Call Flow Control: **green** (the path a call takes right now), **red** (not taken). Arrows stay **black** when dpviz cannot determine the state — for example a Time Condition using a Calendar while the Calendar module is disabled.

### Highlighting Call Paths
- Click **Highlight Paths**, then select a node or edge to highlight it (links are inactive).
- To clear highlights, click **Remove Highlights**.

### Sanitize Labels
- Click **Sanitize Labels** to hide all node labels with black bars. Links will be temporarily disabled.
- You can click individual nodes or the header to reveal their labels while in sanitize mode.
- Enter a filename and download the sanitized `.png` for safe sharing online.
- To remove all black bars and restore the original labels, click **Restore Labels**.

### Save View
- Hold **CTRL** or **SHIFT** while clicking nodes to modify the view before saving.
- Click **Save View** to provide a description for the current view.
- If you are viewing a previously saved view, clicking **Save View** allows you to **rename** or **delete** it.


## Navigation
- **Reload Button:** Redraw the current dial plan to reflect any changes that were made to the phone system.
- **Next / Previous buttons:** Select and view the next or previous menu option. Honors filtered search text.
- **Ignore / hide from a Node:** Press Shift and left-click a node to make it the last node drawn. Helpful for focusing on important routes. (eg. Time condition flows into another time condition.) Multiple "shift + clicks" are supported.
- **Redraw from a Node:** Press Ctrl (Cmd on macOS) and left-click a node to make it the new starting point in the diagram. To revert, Ctrl/Cmd + left-click the "Back" node.
- **Pan** by holding down the left mouse button and dragging.
- **Zoom** using the mouse wheel. The wheel scrolls the page as usual until you click the graph to engage zoom; click anywhere outside the graph to release it again. Requires **Pan & Zoom** to be enabled in Settings.

### Keyboard Shortcuts
- **R** – Reload the current graph.
- **← / →** – Jump to the previous or next dial plan.
- **F** – Fit the graph: reset pan and zoom back to the default view.
- **?** – Open the Navigation & Usage page.

Shortcuts work as soon as the page loads, and pause automatically while you are typing in a field, searching the dial plan list, or when a dialog is open.

## Additional Features
- **Listen** to recordings assigned to Announcement, Dynamic Route, IVR, Play Recording, Voicemail modules. (**Note**: Supports multi-part and multi-language recordings. Only .wav files are supported.)
- **Hover** over a path to highlight the route between destinations. **Click** the path to keep it highlighted.
- **Click** on a destination to open it in a new tab.
- **Click** on a "Match: (timegroup)" or "No Match" to open it in a new tab.
- **Export** the dial plan with standard or custom filename. Choose between super, high, standard quality, or .SVG
- **Simulate Date & Time** (hamburger menu) to see which path a call would take at another moment. Time Conditions and Calendars are both evaluated against the simulated time, so you can check holiday and after-hours routing before the date arrives.

## Dependencies
- **PHP >= 5.6.0**
- **Recordings**
- **Sound Languages**

## Supported PBXs
- **FreePBX 14 - 17**
- **PBXact**
- **TangoPBX**
- **IncrediblePBX 2027, 2025, & 2026**

## Supported Languange Translations
- **Chinese (Simplified)**
- **Dutch (Netherlands)**
- **French**
- **German**
- **Italian**
- **Japanese**
- **Portuguese (Brazil)**
- **Portuguese (Portugal)**
- **Russian**
- **Spanish (Spain)**

## Support
dpviz is free and always will be. If it saves you time, you can [☕ buy me a coffee](https://buymeacoffee.com/adamvolchko).

## License
This module's code is licensed under [GPLv3+](http://www.gnu.org/licenses/gpl-3.0.txt).

