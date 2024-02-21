# gpxFixer

Simple application for fixing broken tracks in GPX format. Usually it is tracks from "Navitel" navigators. 
Algorithm is rather simple - GPX track is an XML file and application just cleans it up using Tidy library and gives it back to user. If app can't fix it, it gives me know for I can enhance it.