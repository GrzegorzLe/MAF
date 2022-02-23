/**
 * Debug helper output JavaScript file
 * 

	Copyright 2009 Grzegorz Lesniewski <grzegorz.lesniewski@gmail.com>

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

		http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.
 */
/* code hevily based on ColdFusion's cfdump code */
function _dbg_toggleRow( source )
{
	var target = ( document.all ) ? source.parentElement.cells[ 1 ] : source.parentNode.lastChild;
	_dbg_toggleTarget( target, _dbg_toggleSource( source ) );
}

function _dbg_toggleSource( source )
{
	if ( source.style.fontStyle == 'italic' )
	{
		source.style.fontStyle = 'normal';
		source.title = 'click to collapse';
		return 'open';
	}
	else
	{
		source.style.fontStyle = 'italic';
		source.title = 'click to expand';
		return 'closed';
	}
}

function _dbg_toggleTarget( target, switchToState )
{
	target.style.display = ( switchToState == 'open' ) ? '' : 'none';
}

function _dbg_toggleTable( source )
{
	var switchToState = _dbg_toggleSource( source );
	if ( document.all )
	{
		var table = source.parentElement.parentElement;
		for ( var i=1; i<table.rows.length; i++ )
		{
			target = table.rows[ i ];
			_dbg_toggleTarget( target, switchToState );
		}
	}
	else
	{
		var table = source.parentNode.parentNode;
		for ( var i = 1; i < table.childNodes.length; i++ )
		{
			target = table.childNodes[ i ];
			if( target.style )
				_dbg_toggleTarget( target, switchToState );
		}
	}
}
